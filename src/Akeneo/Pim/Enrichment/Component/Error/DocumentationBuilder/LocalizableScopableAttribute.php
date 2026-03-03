<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndScopableAttributeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LocalizableScopableAttribute implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        return $object instanceof LocalizableAndNotScopableAttributeException ||
            $object instanceof LocalizableAndScopableAttributeException ||
            $object instanceof NotLocalizableAndScopableAttributeException ||
            $object instanceof NotLocalizableAndNotScopableAttributeException;
    }

    public function buildDocumentation($object): DocumentationCollection
    {
        if (false === $this->support($object)) {
            throw new \InvalidArgumentException('Parameter $object is not supported.');
        }

        return new DocumentationCollection([
            new Documentation(
                'Please check your {attribute_settings}.',
                [
                    'attribute_settings' => new RouteMessageParameter(
                        sprintf('%s attributes settings', $object->getAttributeCode()),
                        'pim_enrich_attribute_edit',
                        ['code' => $object->getAttributeCode()]
                    )
                ],
                Documentation::STYLE_TEXT
            ),
            new Documentation(
                'More information about attributes: {manage_attribute}.',
                [
                    'manage_attribute' => new HrefMessageParameter(
                        'Manage your attributes',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html'
                    )
                ],
                Documentation::STYLE_INFORMATION
            )
        ]);
    }
}
