<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
interface ValueExtractorInterface
{
    public const SOURCE_TYPE_ATTRIBUTE_ASSET_COLLECTION = 'pim_catalog_asset_collection';
    public const SOURCE_TYPE_ATTRIBUTE_BOOLEAN = 'pim_catalog_boolean';
    public const SOURCE_TYPE_ATTRIBUTE_DATE = 'pim_catalog_date';
    public const SOURCE_TYPE_ATTRIBUTE_IDENTIFIER = 'pim_catalog_identifier';
    public const SOURCE_TYPE_ATTRIBUTE_IMAGE = 'pim_catalog_image';
    public const SOURCE_TYPE_ATTRIBUTE_MULTI_SELECT = 'pim_catalog_multiselect';
    public const SOURCE_TYPE_ATTRIBUTE_NUMBER = 'pim_catalog_number';
    public const SOURCE_TYPE_ATTRIBUTE_PRICE_COLLECTION = 'pim_catalog_price_collection';
    public const SOURCE_TYPE_ATTRIBUTE_REFERENCE_ENTITY = 'akeneo_reference_entity';
    public const SOURCE_TYPE_ATTRIBUTE_SIMPLE_SELECT = 'pim_catalog_simpleselect';
    public const SOURCE_TYPE_ATTRIBUTE_TEXT = 'pim_catalog_text';
    public const SOURCE_TYPE_ATTRIBUTE_TEXTAREA = 'pim_catalog_textarea';
    public const SOURCE_TYPE_ATTRIBUTE_METRIC = 'pim_catalog_metric';

    public const SUB_SOURCE_TYPE_ATTRIBUTE_TEXT = 'text';
    public const SUB_SOURCE_TYPE_ATTRIBUTE_MEDIA_FILE = 'media_file';

    public const SOURCE_TYPE_CATEGORIES = 'categories';
    public const SOURCE_TYPE_FAMILY = 'family';
    public const SOURCE_TYPE_STATUS = 'status';

    public const TARGET_TYPE_ARRAY_OF_STRINGS = 'array<string>';
    public const TARGET_TYPE_BOOLEAN = 'boolean';
    public const TARGET_TYPE_NUMBER = 'number';
    public const TARGET_TYPE_STRING = 'string';

    public const TARGET_FORMAT_DATETIME = 'date-time';
    public const TARGET_FORMAT_URI = 'uri';

    /**
     * @param RawProduct $product
     * @param array<string, mixed>|null $parameters
     */
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): mixed;

    public function getSupportedSourceType(): string;

    public function getSupportedSubSourceType(): ?string;

    public function getSupportedTargetType(): string;

    public function getSupportedTargetFormat(): ?string;
}
