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

namespace Akeneo\Platform\Syndication\Test\Acceptance\UseCases\Property;

use Akeneo\Platform\Syndication\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\Syndication\Application\Common\Selection\Enabled\EnabledSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\MapValues\MapValuesQuery;
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
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it replaces enabled value' => [
                'operations' => [
                    new ReplacementOperation([
                        'true' => 'active',
                        'false' => 'inactive',
                    ]),
                ],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(true),
                'expected' => [self::TARGET_NAME => 'active'],
            ],
            'it replaces disabled value' => [
                'operations' => [
                    new ReplacementOperation([
                        'true' => 'active',
                        'false' => 'inactive',
                    ]),
                ],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(false),
                'expected' => [self::TARGET_NAME => 'inactive'],
            ],
            'it fallbacks on the default disabled value when mapping not found' => [
                'operations' => [
                    new ReplacementOperation([
                        'true' => 'active',
                    ]),
                ],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(false),
                'expected' => [self::TARGET_NAME => '0'],
            ],
            'it selects the disabled value' => [
                'operations' => [],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(false),
                'expected' => [self::TARGET_NAME => '0'],
            ],
            'it selects the enabled value' => [
                'operations' => [],
                'selection' => new EnabledSelection(),
                'value' => new EnabledValue(true),
                'expected' => [self::TARGET_NAME => '1'],
            ],
        ];
    }
}
