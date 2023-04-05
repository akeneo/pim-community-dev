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
     * @dataProvider validConversionProvider
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

    public function validConversionProvider(): array
    {
        return [
            'array<string>' => [
                'array<string>',
                '',
                [
                    'categories',
                    'pim_catalog_multiselect',
                ],
            ],
            'boolean' => [
                'boolean',
                '',
                [
                    'pim_catalog_boolean',
                    'status',
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

    public function testItThrowsANoCompatibleAttributeTypeException(): void
    {
        $this->expectException(NoCompatibleAttributeTypeFoundException::class);
        $this->targetTypeConverter->toAttributeTypes('unexpected_type');
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
