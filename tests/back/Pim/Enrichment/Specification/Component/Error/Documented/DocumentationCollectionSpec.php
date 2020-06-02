<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\Documented;

use Akeneo\Pim\Enrichment\Component\Error\Documented\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documented\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documented\MessageParameterTypes;
use Akeneo\Pim\Enrichment\Component\Error\Documented\RouteMessageParameter;
use PhpSpec\ObjectBehavior;

class DocumentationCollectionSpec extends ObjectBehavior
{
    public function it_normalizes_the_information(): void
    {
        $this->beConstructedWith([
            new Documentation(
                'More information about attributes: {what_is_attribute} {manage_attribute}.',
                [
                    new HrefMessageParameter(
                        'What is an attribute?',
                        'https://help.akeneo.com/what-is-an-attribute.html',
                        '{what_is_attribute}'
                    ),
                    new HrefMessageParameter(
                        'Manage your attributes',
                        'https://help.akeneo.com/manage-your-attributes.html',
                        '{manage_attribute}'
                    )
                ]
            ),
            new Documentation(
                'Please check your {attribute_settings}.',
                [
                    new RouteMessageParameter(
                        'Attributes settings',
                        'pim_enrich_attribute_index',
                        '{attribute_settings}'
                    )
                ]
            )
        ]);
        $this->normalize()->shouldReturn([
            [
                'message' => 'More information about attributes: {what_is_attribute} {manage_attribute}.',
                'parameters' => [
                    '{what_is_attribute}' => [
                        'type' => MessageParameterTypes::HREF,
                        'href' => 'https://help.akeneo.com/what-is-an-attribute.html',
                        'title' => 'What is an attribute?',
                        'needle' => '{what_is_attribute}',
                    ],
                    '{manage_attribute}' => [
                        'type' => MessageParameterTypes::HREF,
                        'href' => 'https://help.akeneo.com/manage-your-attributes.html',
                        'title' => 'Manage your attributes',
                        'needle' => '{manage_attribute}',
                    ],
                ]
            ],
            [
                'message' => 'Please check your {attribute_settings}.',
                'parameters' => [
                    '{attribute_settings}' => [
                        'type' => MessageParameterTypes::ROUTE,
                        'route' => 'pim_enrich_attribute_index',
                        'routeParameters' => [],
                        'title' => 'Attributes settings',
                        'needle' => '{attribute_settings}',
                    ],
                ]
            ]
        ]);
    }

    public function it_validates_documentations(): void
    {
        $documentation = new class() {};
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
