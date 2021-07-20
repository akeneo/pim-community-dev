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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\FamilyVariant\FamilyVariantCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\FamilyVariant\FamilyVariantLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\FamilyVariant\InMemoryGetFamilyVariantTranslations;
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
        $productMapper = $this->getProductMapper();
        $this->loadFamilyVariantLabels();

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
                'selection' => new FamilyVariantCodeSelection(),
                'value' => new FamilyVariantValue('pants_size'),
                'expected' => [static::TARGET_NAME => 'pants_size']
            ],
            [
                'operations' => [],
                'selection' => new FamilyVariantLabelSelection('en_US'),
                'value' => new FamilyVariantValue('pants_size'),
                'expected' => [static::TARGET_NAME => '[pants_size]']
            ],
            [
                'operations' => [],
                'selection' => new FamilyVariantLabelSelection('fr_FR'),
                'value' => new FamilyVariantValue('pants_size'),
                'expected' => [static::TARGET_NAME => 'Pantalons']
            ]
        ];
    }

    private function loadFamilyVariantLabels()
    {
        /** @var InMemoryGetFamilyVariantTranslations $familyVariantLabelsRepository */
        $familyVariantLabelsRepository = self::$container->get('akeneo.pim.structure.query.get_family_variant_translations');
        $familyVariantLabelsRepository->addFamilyVariantLabel('pants_size', 'fr_FR', 'Pantalons');
    }
}
