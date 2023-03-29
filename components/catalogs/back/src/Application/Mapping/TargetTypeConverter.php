<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping;

use Akeneo\Catalogs\Application\Exception\NoCompatibleAttributeTypeFoundException;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProductMappingSchemaTarget from GetProductMappingSchemaQueryInterface
 */
final class TargetTypeConverter
{
    private const PIM_ATTRIBUTE_TYPES = [
        'array<string>' => [
            'categories',
            'pim_catalog_asset_collection',
            'pim_catalog_multiselect',
        ],
        'boolean' => [
            'pim_catalog_boolean',
            'status',
        ],
        'number' => [
            'pim_catalog_metric',
            'pim_catalog_number',
            'pim_catalog_price_collection',
        ],
        'string' => [
            'categories',
            'family',
            'pim_catalog_identifier',
            'pim_catalog_multiselect',
            'pim_catalog_number',
            'pim_catalog_simpleselect',
            'pim_catalog_text',
            'pim_catalog_textarea',
        ],
        'string+date-time' => [
            'pim_catalog_date',
        ],
        'string+uri' => [
            'akeneo_reference_entity',
            'pim_catalog_asset_collection',
            'pim_catalog_image',
        ],
    ];

    private const PIM_ASSET_ATTRIBUTE_TYPES = [
        'array<string>' => [
            'text',
        ],
        'string+uri' => [
            'media_file',
        ],
    ];

    private const PIM_REFERENCE_ENTITY_ATTRIBUTE_TYPES = [
        'string+uri' => [
            'image',
        ],
    ];

    /**
     * @return string[]
     */
    public function toAttributeTypes(string $targetType, string $targetFormat = ''): array
    {
        $key = $targetType;

        if ('' !== $targetFormat) {
            $key = \sprintf('%s+%s', $targetType, $targetFormat);
        }

        if (!isset(self::PIM_ATTRIBUTE_TYPES[$key])) {
            throw new NoCompatibleAttributeTypeFoundException();
        }

        return self::PIM_ATTRIBUTE_TYPES[$key];
    }

    /**
     * @return string[]
     */
    public function toAssetAttributeTypes(string $targetType, string $targetFormat = ''): array
    {
        $key = $targetType;

        if ('' !== $targetFormat) {
            $key = \sprintf('%s+%s', $targetType, $targetFormat);
        }

        if (!isset(self::PIM_ASSET_ATTRIBUTE_TYPES[$key])) {
            throw new NoCompatibleAttributeTypeFoundException();
        }

        return self::PIM_ASSET_ATTRIBUTE_TYPES[$key];
    }

    /**
     * @return string[]
     */
    public function toReferenceEntityAttributeTypes(string $targetType, string $targetFormat = ''): array
    {
        $key = $targetType;

        if ('' !== $targetFormat) {
            $key = \sprintf('%s+%s', $targetType, $targetFormat);
        }

        if (!isset(self::PIM_REFERENCE_ENTITY_ATTRIBUTE_TYPES[$key])) {
            throw new NoCompatibleAttributeTypeFoundException();
        }

        return self::PIM_REFERENCE_ENTITY_ATTRIBUTE_TYPES[$key];
    }

    /**
     * @param ProductMappingSchemaTarget $target
     */
    public function getTargetTypeKey(array $target): string
    {
        $key = $this->flattenTargetType($target);

        $format = $target['format'] ?? '';

        if ('' !== $format) {
            $key = \sprintf('%s+%s', $key, $format);
        }

        return $key;
    }

    /**
     * @param ProductMappingSchemaTarget $target
     */
    public function flattenTargetType(array $target): string
    {
        if ('array' !== $target['type']) {
            return $target['type'];
        }

        return \sprintf('%s<%s>', $target['type'], $target['items']['type'] ?? '');
    }
}
