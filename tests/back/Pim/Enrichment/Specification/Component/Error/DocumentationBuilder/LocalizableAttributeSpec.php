<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\LocalizableAttribute;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAttributeException;
use PhpSpec\ObjectBehavior;

class LocalizableAttributeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(LocalizableAttribute::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_the_error_localizable_attribute()
    {
        $localizableException = LocalizableAttributeException::withCode('attribute_code');
        $notLocalizableException = NotLocalizableAttributeException::withCode('attribute_code');

        $this->support($localizableException)->shouldReturn(true);
        $this->support($notLocalizableException)->shouldReturn(true);
    }

    function it_does_not_support_other_types_of_error()
    {
        $exception = new \Exception();

        $this->support($exception)->shouldReturn(false);
    }

    function it_builds_the_documentation()
    {
        $exception = LocalizableAttributeException::withCode('attribute_code');

        $documentation = $this->buildDocumentation($exception);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'Please check your {channels_settings} or the {attribute_edit_route}.',
                'parameters' => [
                    'channels_settings' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_channel_rest_index',
                        'routeParameters' => [],
                        'title' => 'Channel settings',
                    ],
                    'attribute_edit_route' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_attribute_edit',
                        'routeParameters' => ['code' => 'attribute_code'],
                        'title' => 'attribute_code attributes settings',
                    ],
                ],
                'style' => 'text'
            ],
            [
                'message' => 'More information about channels and locales: {enable_locale} {add_locale}',
                'parameters' => [
                    'enable_locale' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-locales.html#how-to-enabledisable-a-locale',
                        'title' => 'How to enable a locale?',
                    ],
                    'add_locale' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-locales.html#how-to-add-a-new-locale',
                        'title' => 'How to add a new locale?',
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
