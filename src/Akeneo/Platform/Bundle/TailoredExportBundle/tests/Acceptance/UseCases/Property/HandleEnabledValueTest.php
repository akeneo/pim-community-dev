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

use Akeneo\Platform\TailoredExport\Application\Query\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Enabled\EnabledSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use PHPUnit\Framework\Assert;

final class HandleEnabledValueTest extends PropertyTestCase
{
    public const PROPERTY_NAME = 'enabled';

    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_an_enabled_value(
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
                'operations' => [
                    ReplacementOperation::createFromNormalized(
                        [
                            'mapping' => [
                                'true' => 'active',
                                'false' => 'inactive'
                            ]
                        ]
                    )
                ],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(true),
                'expected' => [self::TARGET_NAME => 'active']
            ],
            [
                'operations' => [
                    ReplacementOperation::createFromNormalized(
                        [
                            'mapping' => [
                                'true' => 'active',
                                'false' => 'inactive'
                            ]
                        ]
                    )
                ],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(false),
                'expected' => [self::TARGET_NAME => 'inactive']
            ],
            [
                'operations' => [
                    ReplacementOperation::createFromNormalized(
                        [
                            'mapping' => [
                                'true' => 'active',
                            ]
                        ]
                    )
                ],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(false),
                'expected' => [self::TARGET_NAME => '0']
            ],
            [
                'operations' => [],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(false),
                'expected' => [self::TARGET_NAME => '0']
            ],
            [
                'operations' => [],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(true),
                'expected' => [self::TARGET_NAME => '1']
            ]
        ];
    }
}
