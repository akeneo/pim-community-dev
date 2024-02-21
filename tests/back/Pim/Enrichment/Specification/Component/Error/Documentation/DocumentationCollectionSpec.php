<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\Documentation;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterTypes;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use PhpSpec\ObjectBehavior;

class DocumentationCollectionSpec extends ObjectBehavior
{
    public function it_normalizes_the_information(): void
    {
        $this->beConstructedWith([
            new Documentation(
                'More information about attributes: {what_is_attribute} {manage_attribute}.',
                [
                    'what_is_attribute' => new HrefMessageParameter(
                        'What is an attribute?',
                        'https://help.akeneo.com/what-is-an-attribute.html'
                    ),
                    'manage_attribute' => new HrefMessageParameter(
                        'Manage your attributes',
                        'https://help.akeneo.com/manage-your-attributes.html'
                    )
                ],
                Documentation::STYLE_INFORMATION
            ),
            new Documentation(
                'Please check your {attribute_settings}.',
                [
                    'attribute_settings' => new RouteMessageParameter(
                        'Attributes settings',
                        'pim_enrich_attribute_index',
                    )
                ],
                Documentation::STYLE_TEXT
            )
        ]);
        $this->normalize()->shouldReturn([
            [
                'message' => 'More information about attributes: {what_is_attribute} {manage_attribute}.',
                'parameters' => [
                    'what_is_attribute' => [
                        'type' => MessageParameterTypes::HREF,
                        'href' => 'https://help.akeneo.com/what-is-an-attribute.html',
                        'title' => 'What is an attribute?',
                    ],
                    'manage_attribute' => [
                        'type' => MessageParameterTypes::HREF,
                        'href' => 'https://help.akeneo.com/manage-your-attributes.html',
                        'title' => 'Manage your attributes',
                    ],
                ],
                'style' => 'information'
            ],
            [
                'message' => 'Please check your {attribute_settings}.',
                'parameters' => [
                    'attribute_settings' => [
                        'type' => MessageParameterTypes::ROUTE,
                        'route' => 'pim_enrich_attribute_index',
                        'routeParameters' => [],
                        'title' => 'Attributes settings',
                    ],
                ],
                'style' => 'text'
            ]
        ]);
    }

    public function it_validates_documentations(): void
    {
        $documentation = new class ()
        {
        };
        $this->beConstructedWith([$documentation]);
        $this
            ->shouldThrow(
                new \InvalidArgumentException(sprintf(
                    'Class "%s" can only contain collection of "%s", instance of "%s" given.',
                    DocumentationCollection::class,
                    Documentation::class,
                    get_class($documentation)
                ))
            )
            ->duringInstantiation();
    }
}
