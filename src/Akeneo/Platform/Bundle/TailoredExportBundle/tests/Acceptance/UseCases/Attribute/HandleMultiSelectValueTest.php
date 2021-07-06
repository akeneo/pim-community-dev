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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\MultiSelect\MultiSelectCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\MultiSelect\MultiSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\AttributeOption\InMemoryFindAttributeOptionLabels;
use PHPUnit\Framework\Assert;

final class HandleMultiSelectValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_multiselect_value(
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
                'selection' => new MultiSelectCodeSelection('/'),
                'value' => new MultiSelectValue(['cotton', 'wool']),
                'expected' => [self::TARGET_NAME => 'cotton/wool']
            ],
            [
                'operations' => [],
                'selection' => new MultiSelectLabelSelection('/', 'fr_FR', 'material'),
                'value' => new MultiSelectValue(['cotton', 'wool']),
                'expected' => [self::TARGET_NAME => '[cotton]/Laine']
            ],
        ];
    }

    private function loadOptions()
    {
        /** @var InMemoryFindAttributeOptionLabels $attributeOptionLabels */
        $attributeOptionLabels = self::$container->get('Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface');
        $attributeOptionLabels->addAttributeLabel('material', 'cotton', 'en_US', 'Cotton');
        $attributeOptionLabels->addAttributeLabel('material', 'wool', 'fr_FR', 'Laine');
    }
}
