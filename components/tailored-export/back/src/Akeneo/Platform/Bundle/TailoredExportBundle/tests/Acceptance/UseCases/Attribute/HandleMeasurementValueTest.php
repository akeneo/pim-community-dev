<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Attribute;

use Akeneo\Platform\TailoredExport\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\MeasurementConversionOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\MeasurementRoundingOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementUnitCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementUnitLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementUnitSymbolSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueAndUnitLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement\MeasurementValueSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement\InMemoryFindUnitLabel;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement\InMemoryFindUnitSymbol;
use PHPUnit\Framework\Assert;

final class HandleMeasurementValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_measurement_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();
        $this->loadOptions();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects the value and applies the provided decimal separator' => [
                'operations' => [],
                'selection' => new MeasurementValueSelection(','),
                'value' => new MeasurementValue('10.4', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10,4'],
            ],
            'it selects the unit code' => [
                'operations' => [],
                'selection' => new MeasurementUnitCodeSelection(),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => 'KILOGRAM'],
            ],
            'it selects the unit symbol' => [
                'operations' => [],
                'selection' => new MeasurementUnitSymbolSelection('Weight'),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => 'Kg'],
            ],
            'it selects the unit label' => [
                'operations' => [],
                'selection' => new MeasurementUnitLabelSelection('Weight', 'fr_FR'),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => 'Kilogramme'],
            ],
            'it fallbacks on unit code when unit label is not found' => [
                'operations' => [],
                'selection' => new MeasurementUnitLabelSelection('Weight', 'fr_FR'),
                'value' => new MeasurementValue('10', 'GRAM'),
                'expected' => [self::TARGET_NAME => '[GRAM]'],
            ],
            'it selects the value and unit label then applies the provided decimal separator and locale' => [
                'operations' => [],
                'selection' => new MeasurementValueAndUnitLabelSelection(',', 'Weight', 'fr_FR'),
                'value' => new MeasurementValue('10.5', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10,5 Kilogramme'],
            ],
            'it selects the value and unit label then applies the provided decimal separator and fallbacks on unit code if label is not found' => [
                'operations' => [],
                'selection' => new MeasurementValueAndUnitLabelSelection(',', 'Weight', 'fr_FR'),
                'value' => new MeasurementValue('8.4', 'GRAM'),
                'expected' => [self::TARGET_NAME => '8,4 [GRAM]'],
            ],
            'it selects the value and unit symbol then applies the provided decimal separator' => [
                'operations' => [],
                'selection' => new MeasurementValueAndUnitLabelSelection(',', 'Weight', 'fr_FR'),
                'value' => new MeasurementValue('10.5', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10,5 Kilogramme'],
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new MeasurementValueSelection('.'),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a'],
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new MeasurementValueSelection('.'),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10'],
            ],
            'it selects the value and applies the conversion operation' => [
                'operations' => [
                    new MeasurementConversionOperation('Weight', 'GRAM'),
                ],
                'selection' => new MeasurementValueSelection(','),
                'value' => new MeasurementValue('10.4123', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10412,3'],
            ],
            'it selects the value and applies the standard rounding operation' => [
                'operations' => [
                    new MeasurementRoundingOperation('standard', 2),
                ],
                'selection' => new MeasurementValueSelection(','),
                'value' => new MeasurementValue('10.417', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10,42'],
            ],
            'it selects the value and applies the rounding up operation' => [
                'operations' => [
                    new MeasurementRoundingOperation('round_up', 2),
                ],
                'selection' => new MeasurementValueSelection(','),
                'value' => new MeasurementValue('10.412', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10,42'],
            ],
            'it selects the value and applies the rounding down operation' => [
                'operations' => [
                    new MeasurementRoundingOperation('round_down', 2),
                ],
                'selection' => new MeasurementValueSelection(','),
                'value' => new MeasurementValue('10.417', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10,41'],
            ],
        ];
    }

    private function loadOptions(): void
    {
        /** @var InMemoryFindUnitLabel $findUnitLabel */
        $findUnitLabel = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindUnitLabelInterface');
        $findUnitLabel->addUnitLabel('Weight', 'KILOGRAM', 'fr_FR', 'Kilogramme');
        $findUnitLabel->addUnitLabel('Weight', 'KILOGRAM', 'en_US', 'Kilogram');

        /** @var InMemoryFindUnitSymbol $findUnitSymbol */
        $findUnitSymbol = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindUnitSymbolInterface');
        $findUnitSymbol->addUnitSymbol('Weight', 'KILOGRAM', 'Kg');
        $findUnitSymbol->addUnitSymbol('Weight', 'GRAM', 'g');
    }
}
