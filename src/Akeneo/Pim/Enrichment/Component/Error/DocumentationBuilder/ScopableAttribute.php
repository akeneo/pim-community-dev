<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ScopableAttributeException;

class ScopableAttribute implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        return $object instanceof ScopableAttributeException;
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
                'More information about channels: {manage_channel}',
                [
                    'manage_channel' => new HrefMessageParameter(
                        'Manage your channels',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-channels.html'
                    ),
                ],
                Documentation::STYLE_INFORMATION
            )
        ]);
    }
}
