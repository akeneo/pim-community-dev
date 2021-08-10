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

use Akeneo\Platform\TailoredExport\Application\Query\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementUnitCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementUnitLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementValueSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement\InMemoryFindUnitLabel;
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
        $productMapper = $this->getProductMapper();
        $this->loadOptions();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $productMapper->map($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects the value' => [
                'operations' => [],
                'selection' => new MeasurementValueSelection(),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10']
            ],
            'it selects the unit code' => [
                'operations' => [],
                'selection' => new MeasurementUnitCodeSelection(),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => 'KILOGRAM']
            ],
            'it selects the unit label' => [
                'operations' => [],
                'selection' => new MeasurementUnitLabelSelection('weight', 'fr_FR'),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => 'Kilogramme']
            ],
            'it fallbacks on unit code when unit label is not found' => [
                'operations' => [],
                'selection' => new MeasurementUnitLabelSelection('weight', 'fr_FR'),
                'value' => new MeasurementValue('10', 'GRAM'),
                'expected' => [self::TARGET_NAME => '[GRAM]']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new MeasurementValueSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new MeasurementValueSelection(),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10']
            ],
        ];
    }

    private function loadOptions()
    {
        /** @var InMemoryFindUnitLabel $unitLabels */
        $unitLabels = self::$container->get('Akeneo\Platform\TailoredExport\Domain\Query\FindUnitLabelInterface');
        $unitLabels->addUnitLabel('weight', 'KILOGRAM', 'fr_FR', 'Kilogramme');
        $unitLabels->addUnitLabel('weight', 'KILOGRAM', 'en_US', 'Kilogram');
    }
}
