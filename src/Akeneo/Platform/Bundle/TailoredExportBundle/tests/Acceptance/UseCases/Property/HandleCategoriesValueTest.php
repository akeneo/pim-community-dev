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

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Categories\CategoriesCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Categories\CategoriesLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\CategoriesValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Category\InMemoryFindCategoryLabels;
use PHPUnit\Framework\Assert;

final class HandleCategoriesValueTest extends PropertyTestCase
{
    public const PROPERTY_NAME = 'categories';

    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_categories_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $productMapper = $this->getProductMapper();
        $this->loadCategoryLabels();

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
                'selection' => new CategoriesCodeSelection(','),
                'value' => new CategoriesValue([]),
                'expected' => [self::TARGET_NAME => '']
            ],
            [
                'operations' => [],
                'selection' => new CategoriesCodeSelection(','),
                'value' => new CategoriesValue(['master', 'sales']),
                'expected' => [self::TARGET_NAME => 'master,sales']
            ],
            [
                'operations' => [],
                'selection' => new CategoriesLabelSelection(',', 'fr_FR'),
                'value' => new CategoriesValue(['master', 'sales']),
                'expected' => [self::TARGET_NAME => 'Catalogue principal,[sales]']
            ]
        ];
    }

    private function loadCategoryLabels()
    {
        /** @var InMemoryFindCategoryLabels $categoryLabelsRepository */
        $categoryLabelsRepository = self::$container->get('Akeneo\Platform\TailoredExport\Domain\Query\FindCategoryLabelsInterface');
        $categoryLabelsRepository->addCategoryLabel('master', 'fr_FR', 'Catalogue principal');
    }
}
