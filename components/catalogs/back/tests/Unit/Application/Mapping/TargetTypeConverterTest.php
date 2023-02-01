<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Application\Mapping;

use Akeneo\Catalogs\Application\Exception\NoCompatibleAttributeTypeFoundException;
use Akeneo\Catalogs\Application\Mapping\TargetTypeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            'string' => [
                'string',
                '',
                [
                    'pim_catalog_identifier',
                    'pim_catalog_simpleselect',
                    'pim_catalog_text',
                    'pim_catalog_textarea',
                ],
            ],
            'boolean' => [
                'boolean',
                '',
                [
                    'pim_catalog_boolean',
                ],
            ],
        ];
    }

    public function testItThrowsANoCompatibleAttributeTypeException(): void
    {
        $this->expectException(NoCompatibleAttributeTypeFoundException::class);
        $this->targetTypeConverter->toAttributeTypes('unexpected_type');
    }
}
