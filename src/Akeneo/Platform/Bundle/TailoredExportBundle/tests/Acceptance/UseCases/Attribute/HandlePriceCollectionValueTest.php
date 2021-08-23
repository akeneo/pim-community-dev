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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionAmountSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\Price;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\PriceCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use PHPUnit\Framework\Assert;

final class HandlePriceCollectionValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_price_collection_value(
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
            'it selects the currency codes' => [
                'operations' => [],
                'selection' => new PriceCollectionCurrencyCodeSelection(','),
                'value' => new PriceCollectionValue([new Price('199', 'EUR'), new Price('100', 'USD')]),
                'expected' => [self::TARGET_NAME => 'EUR,USD']
            ],
            'it selects the currency labels' => [
                'operations' => [],
                'selection' => new PriceCollectionCurrencyLabelSelection(',', 'fr_FR'),
                'value' => new PriceCollectionValue([new Price('199', 'EUR'), new Price('100', 'USD')]),
                'expected' => [self::TARGET_NAME => 'euro,dollar des États-Unis']
            ],
            'it selects the amount' => [
                'operations' => [],
                'selection' => new PriceCollectionAmountSelection(','),
                'value' => new PriceCollectionValue([new Price('199', 'EUR'), new Price('100', 'USD')]),
                'expected' => [self::TARGET_NAME => '199,100']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new PriceCollectionCurrencyCodeSelection(','),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new PriceCollectionCurrencyCodeSelection(','),
                'value' => new PriceCollectionValue([new Price('199', 'EUR'), new Price('100', 'USD')]),
                'expected' => [self::TARGET_NAME => 'EUR,USD']
            ],
        ];
    }
}
