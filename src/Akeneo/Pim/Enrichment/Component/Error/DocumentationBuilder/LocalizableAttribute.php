<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAttributeException;

class LocalizableAttribute implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        return $object instanceof LocalizableAttributeException || $object instanceof NotLocalizableAttributeException;
    }

    public function buildDocumentation($object): DocumentationCollection
    {
        if (false === $this->support($object)) {
            throw new \InvalidArgumentException('Parameter $object is not supported.');
        }

        return new DocumentationCollection([
            new Documentation(
                'Please check your {channels_settings} or the {attribute_edit_route}.',
                [
                    'channels_settings' => new RouteMessageParameter(
                        'Channel settings',
                        'pim_enrich_channel_rest_index'
                    ),
                    'attribute_edit_route' => new RouteMessageParameter(
                        sprintf('%s attributes settings', $object->getAttributeCode()),
                        'pim_enrich_attribute_edit',
                        ['code' => $object->getAttributeCode()]
                    )
                ],
                Documentation::STYLE_TEXT
            ),
            new Documentation(
                'More information about channels and locales: {enable_locale} {add_locale}',
                [
                    'enable_locale' => new HrefMessageParameter(
                        'How to enable a locale?',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-locales.html#how-to-enabledisable-a-locale'
                    ),
                    'add_locale' => new HrefMessageParameter(
                        'How to add a new locale?',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-locales.html#how-to-add-a-new-locale'
                    )
                ],
                Documentation::STYLE_INFORMATION
            )
        ]);
    }
}
