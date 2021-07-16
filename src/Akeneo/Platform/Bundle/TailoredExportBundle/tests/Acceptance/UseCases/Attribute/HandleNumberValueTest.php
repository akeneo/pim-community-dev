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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Number\NumberSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\NumberValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use PHPUnit\Framework\Assert;

final class HandleNumberValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_number_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $productMapper = $this->getProductMapper();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $productMapper->map($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it_handles_number_selection' => [
                'operations' => [],
                'selection' => new NumberSelection(','),
                'value' => new NumberValue('10'),
                'expected' => [self::TARGET_NAME => '10']
            ],
            'it_handles_number_with_default_decimal_selection' => [
                'operations' => [],
                'selection' => new NumberSelection('.'),
                'value' => new NumberValue('10.73737443838'),
                'expected' => [self::TARGET_NAME => '10.73737443838']
            ],
            'it_handles_number_with_decimal_selection' => [
                'operations' => [],
                'selection' => new NumberSelection(','),
                'value' => new NumberValue('10.73737443838'),
                'expected' => [self::TARGET_NAME => '10,73737443838']
            ]
        ];
    }
}
