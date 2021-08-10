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

use Akeneo\Platform\TailoredExport\Application\Query\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Groups\GroupsCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Groups\GroupsLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Group\InMemoryFindGroupLabels;
use PHPUnit\Framework\Assert;

final class HandleGroupsValueTest extends PropertyTestCase
{
    public const PROPERTY_NAME = 'groups';

    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_groups_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $productMapper = $this->getProductMapper();
        $this->loadGroupLabels();

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
                'selection' => new GroupsCodeSelection(','),
                'value' => new GroupsValue([]),
                'expected' => [self::TARGET_NAME => '']
            ],
            [
                'operations' => [],
                'selection' => new GroupsCodeSelection(','),
                'value' => new GroupsValue(['tshirt', 'summerSale2021']),
                'expected' => [self::TARGET_NAME => 'tshirt,summerSale2021']
            ],
            [
                'operations' => [],
                'selection' => new GroupsLabelSelection(',', 'en_US'),
                'value' => new GroupsValue(['tshirt', 'summerSale2020', 'summerSale2021']),
                'expected' => [self::TARGET_NAME => 'Tshirt,[summerSale2020],Summer sale 2021']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new GroupsCodeSelection(','),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new GroupsCodeSelection(','),
                'value' => new GroupsValue(['tshirt', 'summerSale2021']),
                'expected' => [self::TARGET_NAME => 'tshirt,summerSale2021']
            ],
        ];
    }

    private function loadGroupLabels()
    {
        /** @var InMemoryFindGroupLabels $groupLabelsRepository */
        $groupLabelsRepository = self::$container->get('Akeneo\Platform\TailoredExport\Domain\Query\FindGroupLabelsInterface');
        $groupLabelsRepository->addGroupLabel('tshirt', 'en_US', 'Tshirt');
        $groupLabelsRepository->addGroupLabel('summerSale2021', 'en_US', 'Summer sale 2021');
    }
}
