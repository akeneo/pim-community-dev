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

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Parent\ParentCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Parent\ParentLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ProductModel\InMemoryFindProductModelLabels;
use PHPUnit\Framework\Assert;

final class HandleParentValueTest extends PropertyTestCase
{
    public const PROPERTY_NAME = 'parent';

    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_parent_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $productMapper = $this->getProductMapper();
        $this->loadParent();

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
                'selection' => new ParentCodeSelection(),
                'value' => new ParentValue("a_product_model_code"),
                'expected' => [self::TARGET_NAME => 'a_product_model_code']
            ],
            [
                'operations' => [],
                'selection' => new ParentLabelSelection('en_US', 'ecommerce'),
                'value' => new ParentValue("a_product_model_code"),
                'expected' => [self::TARGET_NAME => 'A product model']
            ],
            [
                'operations' => [],
                'selection' => new ParentLabelSelection('en_US', 'ecommerce'),
                'value' => new ParentValue("a_product_model_code_without_label"),
                'expected' => [self::TARGET_NAME => '[a_product_model_code_without_label]']
            ],
        ];
    }

    private function loadParent()
    {
        /** @var InMemoryFindProductModelLabels $findProductModelLabelsRepository */
        $findProductModelLabelsRepository = self::$container->get('Akeneo\Platform\TailoredExport\Domain\Query\FindProductModelLabelsInterface');
        $findProductModelLabelsRepository->addProductModelLabel('a_product_model_code', 'ecommerce', 'en_US', 'A product model');
    }
}
