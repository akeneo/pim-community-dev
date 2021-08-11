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

use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsQuantitySelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociation;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociationsValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
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
    public function test_it_can_transform_a_quantified_associations_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();
        $this->loadAssociatedEntityLabels();

        $columnCollection = $this->createSingleSourceColumnCollection(false, $operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        $productAssociations = [new QuantifiedAssociation('1111111171', 3), new QuantifiedAssociation('13620748', 2)];
        $productModelAssociations = [new QuantifiedAssociation('athena', 1), new QuantifiedAssociation('hat', 2)];

        return [
            'Select associated product codes with ";" as separator' => [
                'operations' => [],
                'selection' => new QuantifiedAssociationsCodeSelection('products', ';'),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => '1111111171;13620748']
            ],
            'Select associated product codes with "," as separator' => [
                'operations' => [],
                'selection' => new QuantifiedAssociationsCodeSelection('products', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => '1111111171,13620748']
            ],
            'Select associated product model codes' => [
                'operations' => [],
                'selection' => new QuantifiedAssociationsCodeSelection('product_models', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => 'diana,stilleto']
            ],
            'Select associated product labels with a product without translation' => [
                'operations' => [],
                'selection' => new QuantifiedAssociationsLabelSelection('products', 'ecommerce', 'en_US', ';'),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => 'Bag;[13620748]']
            ],
            'Select associated product labels with a product with "," as separator' => [
                'operations' => [],
                'selection' => new QuantifiedAssociationsLabelSelection('products', 'ecommerce', 'en_US', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => 'Bag,[13620748]']
            ],
            'Select associated product models label with a product model without translation' => [
                'operations' => [],
                'selection' => new QuantifiedAssociationsLabelSelection('product_models', 'ecommerce', 'en_US', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => 'Diana,[stilleto]']
            ],
            'Select associated product quantities' => [
                'operations' => [],
                'selection' => new QuantifiedAssociationsQuantitySelection('products', ','),
                'value' => new QuantifiedAssociationsValue($productAssociations, $productModelAssociations),
                'expected' => [self::TARGET_NAME => '3,2']
            ],
            'Select associated product model quantities' => [
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
