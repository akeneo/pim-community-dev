<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Test\Acceptance\Hydrator\Value\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue as ProductPriceCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\Price;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\PriceCollectionValue;

class PriceCollectionValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_price_collection_value_from_product_value(): void
    {
        $expectedValue = new PriceCollectionValue([
            new Price('12', 'EUR'),
            new Price('13.3', 'USD'),
        ]);

        $productValue = ProductPriceCollectionValue::value(
            'price_collection_attribute_code',
            new PriceCollection([
                new ProductPrice(12, 'EUR'),
                new ProductPrice(13.3, 'USD'),
            ]),
        );

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_price_collection';
    }
}
