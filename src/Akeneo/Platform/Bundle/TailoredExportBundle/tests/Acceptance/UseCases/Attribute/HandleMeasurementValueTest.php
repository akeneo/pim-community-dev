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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementAmountSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementUnitCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementUnitLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\MeasurementValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Measurement\InMemoryGetUnitTranslations;
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
            [
                'operations' => [],
                'selection' => new MeasurementAmountSelection(),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => '10']
            ],
            [
                'operations' => [],
                'selection' => new MeasurementUnitCodeSelection(),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => 'KILOGRAM']
            ],
            [
                'operations' => [],
                'selection' => new MeasurementUnitLabelSelection('weight', 'fr_FR'),
                'value' => new MeasurementValue('10', 'KILOGRAM'),
                'expected' => [self::TARGET_NAME => 'Kilogramme']
            ],
            [
                'operations' => [],
                'selection' => new MeasurementUnitLabelSelection('weight', 'fr_FR'),
                'value' => new MeasurementValue('10', 'GRAM'),
                'expected' => [self::TARGET_NAME => '[GRAM]']
            ],
        ];
    }

    private function loadOptions()
    {
        /** @var InMemoryGetUnitTranslations $unitLabels */
        $unitLabels = self::$container->get('akeneo_measurement.public_api.get_unit_translations');
        $unitLabels->addUnitLabel('weight', 'KILOGRAM', 'fr_FR', 'Kilogramme');
        $unitLabels->addUnitLabel('weight', 'KILOGRAM', 'en_US', 'Kilogram');
    }
}
