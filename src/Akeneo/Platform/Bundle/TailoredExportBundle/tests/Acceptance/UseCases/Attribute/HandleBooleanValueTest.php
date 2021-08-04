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

use Akeneo\Platform\TailoredExport\Application\Query\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use PHPUnit\Framework\Assert;

final class HandleBooleanValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_boolean_value(
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
            [
                'operations' => [],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(true),
                'expected' => [self::TARGET_NAME => '1']
            ],
            [
                'operations' => [],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(false),
                'expected' => [self::TARGET_NAME => '0']
            ],
            [
                'operations' => [
                    ReplacementOperation::createFromNormalized(
                        [
                            'mapping' => [
                                'true' => 'oui',
                                'false' => 'non'
                            ]
                        ]
                    )
                ],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(true),
                'expected' => [self::TARGET_NAME => 'oui']
            ],
            [
                'operations' => [
                    ReplacementOperation::createFromNormalized(
                        [
                            'mapping' => [
                                'true' => 'oui',
                                'false' => 'non'
                            ]
                        ]
                    )
                ],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(false),
                'expected' => [self::TARGET_NAME => 'non']
            ],
            [
                'operations' => [
                    ReplacementOperation::createFromNormalized(
                        [
                            'mapping' => [
                                'true' => 'oui',
                            ]
                        ]
                    )
                ],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(false),
                'expected' => [self::TARGET_NAME => '0']
            ],
        ];
    }
}
