<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\Documentation;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterInterface;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterTypes;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use PhpSpec\ObjectBehavior;

class DocumentationSpec extends ObjectBehavior
{
    public function it_normalizes_the_documentation(): void
    {
        $this->beConstructedWith(
            'More information about attributes: {what_is_attribute} {attribute_settings}.',
            [
                'what_is_attribute' => new HrefMessageParameter(
                    'What is an attribute?',
                    'https://help.akeneo.com/what-is-an-attribute.html'
                ),
                'attribute_settings' => new RouteMessageParameter(
                    'Attributes settings',
                    'pim_enrich_attribute_index'
                )
            ],
            Documentation::STYLE_TEXT
        );

        $this->normalize()->shouldReturn([
            'message' => 'More information about attributes: {what_is_attribute} {attribute_settings}.',
            'parameters' => [
                'what_is_attribute' => [
                    'type' => MessageParameterTypes::HREF,
                    'href' => 'https://help.akeneo.com/what-is-an-attribute.html',
                    'title' => 'What is an attribute?',
                ],
                'attribute_settings' => [
                    'type' => MessageParameterTypes::ROUTE,
                    'route' => 'pim_enrich_attribute_index',
                    'routeParameters' => [],
                    'title' => 'Attributes settings',
                ],
            ],
            'style' => Documentation::STYLE_TEXT
        ]);
    }

    public function it_validates_that_message_parameters_implement_the_good_interface(): void
    {
        $this->beConstructedWith(
            'More information about attributes: {what_is_attribute} {attribute_settings}.',
            [
                'what_is_attribute' => new HrefMessageParameter(
                    'What is an attribute?',
                    'https://help.akeneo.com/what-is-an-attribute.html'
                ),
                'anything' => new class ()
                {
                }
            ],
            Documentation::STYLE_TEXT
        );

        $this
            ->shouldThrow(
                new \InvalidArgumentException(sprintf(
                    'Class "%s" accepts only associative array of "%s" as $messageParameters.',
                    Documentation::class,
                    MessageParameterInterface::class
                ))
            )
            ->duringInstantiation();
    }

    public function it_validates_that_message_parameters_provided_match_parameters_from_message(): void
    {
        $message = 'More information about attributes: {what_is_attribute} {attribute_settings}.';
        foreach (['what_attribute', '{what_is_attribute}'] as $wrongMatch) {
            $this->beConstructedWith(
                $message,
                [
                    $wrongMatch => new HrefMessageParameter(
                        'What is an attribute?',
                        'https://help.akeneo.com/what-is-an-attribute.html'
                    ),
                ],
                Documentation::STYLE_TEXT
            );

            $this
                ->shouldThrow(
                    new \InvalidArgumentException(sprintf(
                        '$messageParameters "%s" not found in $message "%s".',
                        $wrongMatch,
                        $message
                    ))
                )
                ->duringInstantiation();
        }

        $this->beConstructedWith(
            $message,
            [
                new HrefMessageParameter(
                    'What is an attribute?',
                    'https://help.akeneo.com/what-is-an-attribute.html'
                ),
            ],
            Documentation::STYLE_TEXT
        );

        $this
            ->shouldThrow(
                new \InvalidArgumentException(sprintf(
                    '$messageParameters "%s" not found in $message "%s".',
                    0,
                    $message
                ))
            )
            ->duringInstantiation();
    }

    public function it_validates_the_documentation_type(): void
    {
        $this->beConstructedWith(
            'More information about attributes!',
            [],
            'wrong_documentation_type'
        );

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->duringInstantiation();
    }
}
