<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\LocalizableScopableAttribute;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndScopableAttributeException;
use PhpSpec\ObjectBehavior;

class LocalizableScopableAttributeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(LocalizableScopableAttribute::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_the_error_localizable_attribute()
    {
        $localizableScopableException = LocalizableAndScopableAttributeException::fromAttributeChannelAndLocale(
            'attribute_code',
            'ecommerce',
            'en_US'
        );
        $notLocalizableNotScopableException = NotLocalizableAndNotScopableAttributeException::fromAttributeChannelAndLocale(
            'attribute_code',
            'ecommerce',
            'en_US'
        );
        $notLocalizableScopableException = NotLocalizableAndScopableAttributeException::fromAttributeChannelAndLocale(
            'attribute_code',
            'ecommerce',
            'en_US'
        );
        $localizableNotScopableException = LocalizableAndNotScopableAttributeException::fromAttributeChannelAndLocale(
            'attribute_code',
            'ecommerce',
            'en_US'
        );

        $this->support($localizableScopableException)->shouldReturn(true);
        $this->support($notLocalizableNotScopableException)->shouldReturn(true);
        $this->support($notLocalizableScopableException)->shouldReturn(true);
        $this->support($localizableNotScopableException)->shouldReturn(true);
    }

    function it_does_not_support_other_types_of_error()
    {
        $exception = new \Exception();

        $this->support($exception)->shouldReturn(false);
    }

    function it_builds_the_documentation()
    {
        $exception = LocalizableAndScopableAttributeException::fromAttributeChannelAndLocale(
            'attribute_code',
            'ecommerce',
            'en_US'
        );

        $documentation = $this->buildDocumentation($exception);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'Please check your {attribute_settings}.',
                'parameters' => [
                    'attribute_settings' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_attribute_edit',
                        'routeParameters' => ['code' => 'attribute_code'],
                        'title' => 'attribute_code attributes settings',
                    ],
                ],
                'style' => 'text'
            ],
            [
                'message' => 'More information about attributes: {manage_attribute}.',
                'parameters' => [
                    'manage_attribute' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html',
                        'title' => 'Manage your attributes',
                    ],
                ],
                'style' => 'information'
            ]
        ]);
    }

    function it_does_not_build_the_documentation_for_an_unsupported_error(\Exception $exception)
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$exception]);
    }
}
