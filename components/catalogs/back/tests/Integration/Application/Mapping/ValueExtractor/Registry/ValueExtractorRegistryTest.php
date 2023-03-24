<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Registry;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Exception\ValueExtractorNotFoundException;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings\ArrayOfStringsFromAssetCollectionTextAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings\ArrayOfStringsFromMultiSelectAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Boolean\BooleanFromBooleanAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromMetricAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromNumberAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromPriceCollectionAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromCategoriesValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromFamilyValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromIdentifierAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromMultiSelectAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromNumberAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromSimpleSelectAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromTextareaAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromTextAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringDateTime\StringDateTimeFromDateAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringUri\StringUriFromImageAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Registry\ValueExtractorRegistry;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Registry\ValueExtractorRegistry
 */
class ValueExtractorRegistryTest extends IntegrationTestCase
{
    private ?ValueExtractorRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = self::getContainer()->get(ValueExtractorRegistry::class);
    }

    /**
     * @group ce
     *
     * @dataProvider extractorDataProvider
     */
    public function testItFindsTheExtractor(
        string $sourceType,
        ?string $subSourceType,
        string $targetType,
        ?string $targetFormat,
        string $extractorClassName,
    ): void {
        $extractor = $this->registry->find($sourceType, $subSourceType, $targetType, $targetFormat);

        $this->assertEquals($extractorClassName, $extractor::class);
    }

    public function extractorDataProvider(): array
    {
        return [
            ArrayOfStringsFromAssetCollectionTextAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_asset_collection',
                'subSourceType' => 'text',
                'targetType' => 'array<string>',
                'targetFormat' => null,
                'extractorClassName' => ArrayOfStringsFromAssetCollectionTextAttributeValueExtractor::class,
            ],
            ArrayOfStringsFromMultiSelectAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_multiselect',
                'subSourceType' => null,
                'targetType' => 'array<string>',
                'targetFormat' => null,
                'extractorClassName' => ArrayOfStringsFromMultiSelectAttributeValueExtractor::class,
            ],
            BooleanFromBooleanAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_boolean',
                'subSourceType' => null,
                'targetType' => 'boolean',
                'targetFormat' => null,
                'extractorClassName' => BooleanFromBooleanAttributeValueExtractor::class,
            ],
            NumberFromMetricAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_metric',
                'subSourceType' => null,
                'targetType' => 'number',
                'targetFormat' => null,
                'extractorClassName' => NumberFromMetricAttributeValueExtractor::class,
            ],
            NumberFromNumberAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_number',
                'subSourceType' => null,
                'targetType' => 'number',
                'targetFormat' => null,
                'extractorClassName' => NumberFromNumberAttributeValueExtractor::class,
            ],
            NumberFromPriceCollectionAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_price_collection',
                'subSourceType' => null,
                'targetType' => 'number',
                'targetFormat' => null,
                'extractorClassName' => NumberFromPriceCollectionAttributeValueExtractor::class,
            ],
            StringFromCategoriesValueExtractor::class => [
                'sourceType' => 'categories',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromCategoriesValueExtractor::class,
            ],
            StringFromFamilyValueExtractor::class => [
                'sourceType' => 'family',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromFamilyValueExtractor::class,
            ],
            StringFromIdentifierAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_identifier',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromIdentifierAttributeValueExtractor::class,
            ],
            StringFromMultiSelectAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_multiselect',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromMultiSelectAttributeValueExtractor::class,
            ],
            StringFromNumberAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_number',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromNumberAttributeValueExtractor::class,
            ],
            StringFromSimpleSelectAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_simpleselect',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromSimpleSelectAttributeValueExtractor::class,
            ],
            StringFromTextareaAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_textarea',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromTextareaAttributeValueExtractor::class,
            ],
            StringFromTextAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_text',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromTextAttributeValueExtractor::class,
            ],
            StringDateTimeFromDateAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_date',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => 'date-time',
                'extractorClassName' => StringDateTimeFromDateAttributeValueExtractor::class,
            ],
            StringUriFromImageAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_image',
                'subSourceType' => null,
                'targetType' => 'string',
                'targetFormat' => 'uri',
                'extractorClassName' => StringUriFromImageAttributeValueExtractor::class,
            ],
        ];
    }

    public function testItThrowsIfValueExtractorIsNotFound(): void
    {
        $this->expectException(ValueExtractorNotFoundException::class);

        $this->registry->find('pim_catalog_text', null, 'boolean', null);
    }
}
