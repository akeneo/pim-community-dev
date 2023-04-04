<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Application\Mapping;

use Akeneo\Catalogs\Application\Exception\NoCompatibleAttributeTypeFoundException;
use Akeneo\Catalogs\Application\Mapping\TargetTypeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\TargetTypeConverter
 */
class TargetTypeConverterTest extends TestCase
{
    private ?TargetTypeConverter $targetTypeConverter;

    protected function setUp(): void
    {
        $this->targetTypeConverter = new TargetTypeConverter();
    }

    /**
     * @dataProvider validAttributeTypesConversionProvider
     * @param array<string> $expectedAttributeTypes
     */
    public function testItConvertsTargetTypeToAttributeTypes(
        string $targetType,
        string $targetFormat,
        array $expectedAttributeTypes,
    ): void {
        $this->assertEquals(
            $expectedAttributeTypes,
            $this->targetTypeConverter->toAttributeTypes($targetType, $targetFormat),
        );
    }

    public function validAttributeTypesConversionProvider(): array
    {
        return [
            'array<string>' => [
                'array<string>',
                '',
                [
                    'categories',
                    'pim_catalog_asset_collection',
                    'pim_catalog_multiselect',
                ],
            ],
            'array<string+uri>' => [
                'array<string+uri>',
                '',
                [
                    'pim_catalog_asset_collection',
                ],
            ],
            'boolean' => [
                'boolean',
                '',
                [
                    'pim_catalog_boolean',
                ],
            ],
            'number' => [
                'number',
                '',
                [
                    'pim_catalog_metric',
                    'pim_catalog_number',
                    'pim_catalog_price_collection',
                ],
            ],
            'string' => [
                'string',
                '',
                [
                    'categories',
                    'family',
                    'pim_catalog_identifier',
                    'pim_catalog_multiselect',
                    'pim_catalog_number',
                    'pim_catalog_simpleselect',
                    'pim_catalog_text',
                    'pim_catalog_textarea',
                ],
            ],
            'string+uri' => [
                'string',
                'uri',
                [
                    'pim_catalog_asset_collection',
                    'pim_catalog_image',
                ],
            ],
            'string+date-time' => [
                'string',
                'date-time',
                [
                    'pim_catalog_date',
                ],
            ],
        ];
    }

    public function testItThrowsANoCompatibleAttributeTypeExceptionWhenThereAreNoMatchingAttributeTypes(): void
    {
        $this->expectException(NoCompatibleAttributeTypeFoundException::class);
        $this->targetTypeConverter->toAttributeTypes('unexpected_target_type');
    }

    /**
     * @dataProvider validAssetAttributeTypesConversionProvider
     */
    public function testItConvertsTargetTypeToAssetAttributeTypes(
        string $targetType,
        string $targetFormat,
        array $expectedAssetAttributeTypes,
    ): void {
        $this->assertEquals(
            $expectedAssetAttributeTypes,
            $this->targetTypeConverter->toAssetAttributeTypes($targetType, $targetFormat),
        );
    }

    public function validAssetAttributeTypesConversionProvider(): array
    {
        return [
            'array<string>' => [
                'array<string>',
                '',
                [
                    'text',
                ],
            ],
            'array<string+uri>' => [
                'array<string+uri>',
                '',
                [
                    'media_file',
                ],
            ],
            'string' => [
                'string',
                'uri',
                [
                    'media_file',
                ],
            ],
        ];
    }

    public function testItThrowsANoCompatibleAttributeTypeExceptionWhenThereAreNoMatchingAssetAttributeTypes(): void
    {
        $this->expectException(NoCompatibleAttributeTypeFoundException::class);
        $this->targetTypeConverter->toAssetAttributeTypes('unexpected_target_type');
    }

    public function testItDoesNotFlattensNonArrayTargetType(): void
    {
        $targetType = $this->targetTypeConverter->flattenTargetType(['type' => 'string']);

        $this->assertEquals('string', $targetType);
    }

    public function testItFlattensArrayTargetType(): void
    {
        $targetType = $this->targetTypeConverter->flattenTargetType([
            'type' => 'array',
            'items' => [
                'type' => 'string',
            ],
        ]);

        $this->assertEquals('array<string>', $targetType);
    }
}
