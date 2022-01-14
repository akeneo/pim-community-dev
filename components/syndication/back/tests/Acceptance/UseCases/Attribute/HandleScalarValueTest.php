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

use Akeneo\Platform\Syndication\Application\Common\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\String\ExtractOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\String\SplitOperation;
use Akeneo\Platform\Syndication\Application\Common\Selection\Scalar\ScalarSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;
use Akeneo\Platform\Syndication\Application\MapValues\MapValuesQuery;
use PHPUnit\Framework\Assert;

final class HandleScalarValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_reference_entity_value(
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
            'it selects the scalar value' => [
                'operations' => [],
                'selection' => new ScalarSelection(),
                'value' => new StringValue('Sunglasses'),
                'expected' => [self::TARGET_NAME => 'Sunglasses'],
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new ScalarSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a'],
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new ScalarSelection(),
                'value' => new StringValue('Sunglasses'),
                'expected' => [self::TARGET_NAME => 'Sunglasses'],
            ],
            'it does apply not clean html tags operation on the default value' => [
                'operations' => [
                    new DefaultValueOperation('<h1>test</h1>'),
                    new CleanHTMLTagsOperation()
                ],
                'selection' => new ScalarSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => '<h1>test</h1>']
            ],
            'it does apply not extract operation on the default value' => [
                'operations' => [
                    new DefaultValueOperation('<h1>test</h1>'),
                    new ExtractOperation('/([a-z])/')
                ],
                'selection' => new ScalarSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => '<h1>test</h1>']
            ],
            'it does apply not split operation on the default value' => [
                'operations' => [
                    new DefaultValueOperation('test1|test2'),
                    new SplitOperation('|')
                ],
                'selection' => new ScalarSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'test1|test2']
            ]
        ];
    }
}
