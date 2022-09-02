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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Property;

use Akeneo\Platform\TailoredExport\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\FamilyVariant\FamilyVariantCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\FamilyVariant\FamilyVariantLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\FamilyVariant\InMemoryFindFamilyVariantLabel;
use PHPUnit\Framework\Assert;

final class HandleFamilyVariantValueTest extends PropertyTestCase
{
    public const PROPERTY_NAME = 'family_variant';

    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_family_variant_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();
        $this->loadFamilyVariantLabels();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects the family variant code' => [
                'operations' => [],
                'selection' => new FamilyVariantCodeSelection(),
                'value' => new FamilyVariantValue('pants_size'),
                'expected' => [static::TARGET_NAME => 'pants_size'],
            ],
            'it fallbacks on the family variant code when the label is not found' => [
                'operations' => [],
                'selection' => new FamilyVariantLabelSelection('en_US'),
                'value' => new FamilyVariantValue('pants_size'),
                'expected' => [static::TARGET_NAME => '[pants_size]'],
            ],
            'it selects the family variant label' => [
                'operations' => [],
                'selection' => new FamilyVariantLabelSelection('fr_FR'),
                'value' => new FamilyVariantValue('pants_size'),
                'expected' => [static::TARGET_NAME => 'Pantalons'],
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new FamilyVariantLabelSelection('fr_FR'),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a'],
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new FamilyVariantCodeSelection(),
                'value' => new FamilyVariantValue('pants_size'),
                'expected' => [static::TARGET_NAME => 'pants_size'],
            ],
        ];
    }

    private function loadFamilyVariantLabels(): void
    {
        /** @var InMemoryFindFamilyVariantLabel $familyVariantLabelsRepository */
        $familyVariantLabelsRepository = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindFamilyVariantLabelInterface');
        $familyVariantLabelsRepository->addFamilyVariantLabel('pants_size', 'fr_FR', 'Pantalons');
    }
}
