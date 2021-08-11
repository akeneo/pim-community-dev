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

use Akeneo\Platform\TailoredExport\Domain\Model\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\Number\NumberSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NumberValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
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
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it handles number selection' => [
                'operations' => [],
                'selection' => new NumberSelection(','),
                'value' => new NumberValue('10'),
                'expected' => [self::TARGET_NAME => '10']
            ],
            'it handles number with default decimal selection' => [
                'operations' => [],
                'selection' => new NumberSelection('.'),
                'value' => new NumberValue('10.73737443838'),
                'expected' => [self::TARGET_NAME => '10.73737443838']
            ],
            'it handles number with decimal selection' => [
                'operations' => [],
                'selection' => new NumberSelection(','),
                'value' => new NumberValue('10.73737443838'),
                'expected' => [self::TARGET_NAME => '10,73737443838']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new NumberSelection(','),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new NumberSelection(','),
                'value' => new NumberValue('10'),
                'expected' => [self::TARGET_NAME => '10']
            ],
        ];
    }
}
