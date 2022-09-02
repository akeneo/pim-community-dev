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
use Akeneo\Platform\TailoredExport\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\MultiSelect\MultiSelectCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\MultiSelect\MultiSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
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
            'it selects the option codes' => [
                'operations' => [],
                'selection' => new MultiSelectCodeSelection('/'),
                'value' => new MultiSelectValue(['cotton', 'wool']),
                'expected' => [self::TARGET_NAME => 'cotton/wool']
            ],
            'it selects the option labels' => [
                'operations' => [],
                'selection' => new MultiSelectLabelSelection('/', 'fr_FR', 'material'),
                'value' => new MultiSelectValue(['cotton', 'wool']),
                'expected' => [self::TARGET_NAME => '[cotton]/Laine']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new MultiSelectCodeSelection('/'),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new MultiSelectCodeSelection('/'),
                'value' => new MultiSelectValue(['cotton', 'wool']),
                'expected' => [self::TARGET_NAME => 'cotton/wool']
            ],
            'it applies replacement operation on label selection when value is found in the mapping' => [
                'operations' => [
                    new ReplacementOperation([
                        'cotton' => 'amazing cotton',
                    ]),
                ],
                'selection' => new MultiSelectLabelSelection('/', 'fr_FR', 'material'),
                'value' => new MultiSelectValue(['cotton', 'wool'], []),
                'expected' => [self::TARGET_NAME => 'amazing cotton/Laine'],
            ],
            'it applies replacement operation on code selection when value is found in the mapping' => [
                'operations' => [
                    new ReplacementOperation([
                        'cotton' => 'amazing cotton',
                    ]),
                ],
                'selection' => new MultiSelectCodeSelection('/'),
                'value' => new MultiSelectValue(['cotton', 'wool'], []),
                'expected' => [self::TARGET_NAME => 'amazing cotton/wool'],
            ],
            'it does not apply replacement operation on label selection when value is not found in the mapping' => [
                'operations' => [
                    new ReplacementOperation([
                        'cotton' => 'amazing cotton',
                    ]),
                ],
                'selection' => new MultiSelectLabelSelection('/', 'fr_FR', 'material'),
                'value' => new MultiSelectValue(['polyester'], []),
                'expected' => [self::TARGET_NAME => '[polyester]'],
            ],
            'it does not apply replacement operation on code selection when value is not found in the mapping' => [
                'operations' => [
                    new ReplacementOperation([
                        'cotton' => 'amazing cotton',
                    ]),
                ],
                'selection' => new MultiSelectCodeSelection('/'),
                'value' => new MultiSelectValue(['polyester'], []),
                'expected' => [self::TARGET_NAME => 'polyester'],
            ],
        ];
    }

    private function loadOptions(): void
    {
        /** @var InMemoryFindAttributeOptionLabels $attributeOptionLabels */
        $attributeOptionLabels = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface');
        $attributeOptionLabels->addAttributeLabel('material', 'cotton', 'en_US', 'Cotton');
        $attributeOptionLabels->addAttributeLabel('material', 'wool', 'fr_FR', 'Laine');
    }
}
