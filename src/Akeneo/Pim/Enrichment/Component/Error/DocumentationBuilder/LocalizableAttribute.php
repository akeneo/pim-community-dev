<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\LocalizableValues;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LocalizableAttribute implements DocumentationBuilderInterface
{
    const SUPPORTED_CONSTRAINTS_CODES = [
        LocalizableValues::NON_ACTIVE_LOCALE,
        LocalizableValues::INVALID_LOCALE_FOR_CHANNEL
    ];

    public function support($object): bool
    {
        if (
            $object instanceof ConstraintViolationInterface
            && \in_array($object->getCode(), self::SUPPORTED_CONSTRAINTS_CODES)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param ConstraintViolationInterface $object
     */
    public function buildDocumentation($object): DocumentationCollection
    {
        if (false === $this->support($object)) {
            throw new \InvalidArgumentException('Parameter $object is not supported.');
        }

        $attributeCode = $this->getAttributeCode($object);

        return new DocumentationCollection([
            new Documentation(
                'Please check your {channels_settings} or the {attribute_edit_route}.',
                [
                    'channels_settings' => new RouteMessageParameter(
                        'Channel settings',
                        'pim_enrich_channel_index'
                    ),
                    'attribute_edit_route' => new RouteMessageParameter(
                        sprintf('%s attributes settings', $attributeCode),
                        'pim_enrich_attribute_edit',
                        ['code' => $attributeCode]
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

    /**
     * @param ConstraintViolationInterface $object
     */
    private function getAttributeCode($object): string
    {
        if (
            $object instanceof ConstraintViolationInterface
            && \in_array($object->getCode(), self::SUPPORTED_CONSTRAINTS_CODES)
        ) {
            $parameters = $object->getParameters();

            if (!isset($parameters['%attribute_code%'])) {
                throw new \LogicException('ConstraintViolation parameter "%attribute_code%" should be defined.');
            }

            return $parameters['%attribute_code%'];
        }

        throw new \LogicException('Attribute code should be defined.');
    }
}
