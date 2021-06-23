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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;

class PriceCollectionSelectorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['pim_catalog_price_collection']);
    }

    public function it_returns_attribute_type_supported()
    {
        $attribute = $this->createAttribute('pim_catalog_price_collection');
        $this->supports(['type' => 'amount'], $attribute)->shouldReturn(true);
        $this->supports(['type' => 'currency'], $attribute)->shouldReturn(true);
        $this->supports(['type' => 'label'], $attribute)->shouldReturn(false);
    }

    public function it_selects_the_amount(
        ValueInterface $value,
        ProductInterface $entity
    ) {
        $this->beConstructedWith(['pim_catalog_price_collection']);
        $attribute = $this->createAttribute('pim_catalog_price_collection');
        $value->getData()->willReturn(
            new PriceCollection([
                new ProductPrice(40, 'EUR'),
                new ProductPrice(30, 'USD'),
            ])
        );

        $this->applySelection(['type' => 'amount'], $entity, $attribute, $value)->shouldReturn('40, 30');
    }

    public function it_selects_the_currency(
        ValueInterface $value,
        ProductInterface $entity
    ) {
        $attribute = $this->createAttribute('pim_catalog_price_collection');
        $value->getData()->willReturn(
            new PriceCollection([
                new ProductPrice(40, 'EUR'),
                new ProductPrice(30, 'USD'),
            ])
        );

        $this->applySelection(['type' => 'currency'], $entity, $attribute, $value)->shouldReturn('EUR, USD');
    }

    private function createAttribute(string $attributeType): Attribute
    {
        return new Attribute(
            'a_price',
            $attributeType,
            [],
            false,
            false,
            null,
            null,
            null,
            'prices',
            []
        );
    }
}
