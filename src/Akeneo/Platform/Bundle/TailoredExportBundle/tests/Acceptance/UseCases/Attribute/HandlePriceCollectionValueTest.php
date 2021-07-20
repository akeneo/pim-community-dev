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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\PriceCollection\PriceCollectionAmountSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\PriceCollection\PriceCollectionCurrencyCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\Price;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\PriceCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
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
                'selection' => new PriceCollectionCurrencyCodeSelection(','),
                'value' => new PriceCollectionValue([new Price('199', 'EUR'), new Price('100', 'USD')]),
                'expected' => [self::TARGET_NAME => 'EUR,USD']
            ],
            [
                'operations' => [],
                'selection' => new PriceCollectionCurrencyLabelSelection(',', 'fr_FR'),
                'value' => new PriceCollectionValue([new Price('199', 'EUR'), new Price('100', 'USD')]),
                'expected' => [self::TARGET_NAME => 'euro,dollar des États-Unis']
            ],
            [
                'operations' => [],
                'selection' => new PriceCollectionAmountSelection(','),
                'value' => new PriceCollectionValue([new Price('199', 'EUR'), new Price('100', 'USD')]),
                'expected' => [self::TARGET_NAME => '199,100']
            ]
        ];
    }
}
