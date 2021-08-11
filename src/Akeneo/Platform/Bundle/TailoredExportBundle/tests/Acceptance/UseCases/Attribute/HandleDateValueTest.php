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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Date\DateSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\DateValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use PHPUnit\Framework\Assert;

final class HandleDateValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_date_value(
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
            [
                'operations' => [],
                'selection' => new DateSelection('dd-mm-yyyy'),
                'value' => new DateValue(new \DateTime('16-05-2020T02:12:25')),
                'expected' => [self::TARGET_NAME => '16-05-2020']
            ],
            [
                'operations' => [],
                'selection' => new DateSelection('dd/mm/yy'),
                'value' => new DateValue(new \DateTime('16-05-2020T02:12:25')),
                'expected' => [self::TARGET_NAME => '16/05/20']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new DateSelection('dd/mm/yy'),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new DateSelection('dd/mm/yy'),
                'value' => new DateValue(new \DateTime('16-05-2020T02:12:25')),
                'expected' => [self::TARGET_NAME => '16/05/20']
            ],
        ];
    }
}
