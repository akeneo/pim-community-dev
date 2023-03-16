<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Registry;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Exception\ValueExtractorNotFoundException;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings\ArrayOfStringsFromMultiSelectAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Boolean\BooleanFromBooleanAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromNumberAttributeValueExtractor;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number\NumberFromPriceCollectionAttributeValueExtractor;
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
     * @dataProvider extractorDataProvider
     */
    public function testItFindsTheExtractor(
        string $sourceType,
        string $targetType,
        ?string $targetFormat,
        string $extractorClassName,
    ): void {
        $extractor = $this->registry->find($sourceType, $targetType, $targetFormat);

        $this->assertEquals($extractorClassName, $extractor::class);
    }

    public function extractorDataProvider(): array
    {
        return [
            ArrayOfStringsFromMultiSelectAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_multiselect',
                'targetType' => 'array<string>',
                'targetFormat' => null,
                'extractorClassName' => ArrayOfStringsFromMultiSelectAttributeValueExtractor::class,
            ],
            BooleanFromBooleanAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_boolean',
                'targetType' => 'boolean',
                'targetFormat' => null,
                'extractorClassName' => BooleanFromBooleanAttributeValueExtractor::class,
            ],
            NumberFromNumberAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_number',
                'targetType' => 'number',
                'targetFormat' => null,
                'extractorClassName' => NumberFromNumberAttributeValueExtractor::class,
            ],
            NumberFromPriceCollectionAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_price_collection',
                'targetType' => 'number',
                'targetFormat' => null,
                'extractorClassName' => NumberFromPriceCollectionAttributeValueExtractor::class,
            ],
            StringFromFamilyValueExtractor::class => [
                'sourceType' => 'family',
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromFamilyValueExtractor::class,
            ],
            StringFromIdentifierAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_identifier',
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromIdentifierAttributeValueExtractor::class,
            ],
            StringFromMultiSelectAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_multiselect',
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromMultiSelectAttributeValueExtractor::class,
            ],
            StringFromNumberAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_number',
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromNumberAttributeValueExtractor::class,
            ],
            StringFromSimpleSelectAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_simpleselect',
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromSimpleSelectAttributeValueExtractor::class,
            ],
            StringFromTextareaAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_textarea',
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromTextareaAttributeValueExtractor::class,
            ],
            StringFromTextAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_text',
                'targetType' => 'string',
                'targetFormat' => null,
                'extractorClassName' => StringFromTextAttributeValueExtractor::class,
            ],
            StringDateTimeFromDateAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_date',
                'targetType' => 'string',
                'targetFormat' => 'date-time',
                'extractorClassName' => StringDateTimeFromDateAttributeValueExtractor::class,
            ],
            StringUriFromImageAttributeValueExtractor::class => [
                'sourceType' => 'pim_catalog_image',
                'targetType' => 'string',
                'targetFormat' => 'uri',
                'extractorClassName' => StringUriFromImageAttributeValueExtractor::class,
            ],
        ];
    }

    public function testItThrowsIfValueExtractorIsNotFound(): void
    {
        $this->expectException(ValueExtractorNotFoundException::class);

        $this->registry->find('pim_catalog_text', 'boolean', null);
    }
}
