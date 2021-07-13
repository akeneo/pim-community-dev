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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Association;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\QuantifiedAssociations\QuantifiedAssociationsCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\QuantifiedAssociations\QuantifiedAssociationsQuantitySelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\QuantifiedAssociations\QuantifiedAssociationsLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\QuantifiedAssociation;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\QuantifiedAssociationsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Group\InMemoryFindGroupLabels;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Product\InMemoryFindProductLabels;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ProductModel\InMemoryFindProductModelLabels;
use PHPUnit\Framework\Assert;

final class HandleQuantifiedAssociationsValue extends AssociationTestCase
{
    public const ASSOCIATION_TYPE_CODE = 'PACK';

    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_associations_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $productMapper = $this->getProductMapper();
        $this->loadAssociatedEntityLabels();

        $columnCollection = $this->createSingleSourceColumnCollection(false, $operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $productMapper->map($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        $productAssociations = [new QuantifiedAssociation('1111111171', 3), new QuantifiedAssociation('13620748', 2)];
        $productModelAssociations = [new QuantifiedAssociation('athena', 1), new QuantifiedAssociation('hat', 2)];

        return [
            [
                'operations' => [],
                'selection' => new QuantifiedAssociationsCodeSelection('products', ';'),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => '1111111171;13620748']
            ],
            [
                'operations' => [],
                'selection' => new QuantifiedAssociationsCodeSelection('products', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => '1111111171,13620748']
            ],
            [
                'operations' => [],
                'selection' => new QuantifiedAssociationsCodeSelection('product_models', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => 'diana,stilleto']
            ],
            [
                'operations' => [],
                'selection' => new QuantifiedAssociationsLabelSelection('products', 'ecommerce', 'en_US', ';'),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => 'Bag;[13620748]']
            ],
            [
                'operations' => [],
                'selection' => new QuantifiedAssociationsLabelSelection('products', 'ecommerce', 'en_US', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => 'Bag,[13620748]']
            ],
            [
                'operations' => [],
                'selection' => new QuantifiedAssociationsLabelSelection('product_models', 'ecommerce', 'en_US', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => 'Diana,[stilleto]']
            ],
            [
                'operations' => [],
                'selection' => new QuantifiedAssociationsQuantitySelection('products', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => '3,2']
            ],
            [
                'operations' => [],
                'selection' => new QuantifiedAssociationsQuantitySelection('product_models', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => '1,2']
            ],
        ];
    }

    private function loadAssociatedEntityLabels()
    {
        /** @var InMemoryFindProductLabels $productLabelRepository */
        $productLabelRepository = self::$container->get('akeneo.pim.structure.query.get_category_translations');
        $productLabelRepository->addProductLabel('1111111171', 'ecommerce', 'fr_FR', 'Bag');

        /** @var InMemoryFindProductModelLabels $productLabelRepository */
        $productLabelRepository = self::$container->get('akeneo.pim.structure.query.get_category_translations');
        $productLabelRepository->addProductModelLabel('diana', 'ecommerce', 'fr_FR', 'Diana');

        /** @var InMemoryFindGroupLabels $groupLabelRepository */
        $groupLabelRepository = self::$container->get('akeneo.pim.structure.query.get_category_translations');
        $groupLabelRepository->addGroupLabel('summerSale2021', 'fr_FR', 'Summer sale 2021');
    }
}
