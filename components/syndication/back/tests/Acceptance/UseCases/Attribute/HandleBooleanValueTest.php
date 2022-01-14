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

namespace Akeneo\Platform\Syndication\Test\Acceptance\UseCases\Attribute;

use Akeneo\Platform\Syndication\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\Syndication\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\MapValues\MapValuesQuery;
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
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mapValuesQuery = new MapValuesQuery($columnCollection, $valueCollection);
        $mappedProduct = $mapValuesQueryHandler->handle($mapValuesQuery);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects true value' => [
                'operations' => [],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(true),
                'expected' => [self::TARGET_NAME => '1'],
            ],
            'it selects false value' => [
                'operations' => [],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(false),
                'expected' => [self::TARGET_NAME => '0'],
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    new ReplacementOperation([
                        'true' => 'oui',
                        'false' => 'non',
                    ]),
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(true),
                'expected' => [self::TARGET_NAME => 'oui'],
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    new ReplacementOperation([
                        'true' => 'oui',
                        'false' => 'non',
                    ]),
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new BooleanSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a'],
            ],
            'it applies replacement operation when value is found in the mapping' => [
                'operations' => [
                    new ReplacementOperation([
                        'true' => 'oui',
                        'false' => 'non',
                    ]),
                ],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(false),
                'expected' => [self::TARGET_NAME => 'non'],
            ],
            'it does not apply replacement operation when value is not found in the mapping' => [
                'operations' => [
                    new ReplacementOperation([
                        'true' => 'oui',
                    ]),
                ],
                'selection' => new BooleanSelection(),
                'value' => new BooleanValue(false),
                'expected' => [self::TARGET_NAME => '0'],
            ],
        ];
    }
}
