<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker\Attribute;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\CurrencyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class PriceCompleteCheckerSpec extends ObjectBehavior
{
    public function it_suports_price_collection_attribute(
        AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn('other');
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_price_collection(
        ProductValueInterface $value,
        ChannelInterface $channel,
        ArrayCollection $arrayCollection,
        CurrencyInterface $currency1,
        CurrencyInterface $currency2,
        ProductPriceInterface $price1,
        ProductPriceInterface $price2
    ) {
        $channel->getCurrencies()->willReturn($arrayCollection);
        $arrayCollection->toArray()->willReturn([$currency1, $currency2]);

        $currency1->getCode()->willReturn('USD');
        $currency2->getCode()->willReturn('EUR');

        $price1->getCurrency()->willReturn('USD');
        $price2->getCurrency()->willReturn('EUR');
        $price1->getData()->willReturn(666);
        $price2->getData()->willReturn(777);

        $value->getData()->willReturn([$price1, $price2]);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);
    }

    public function it_succesfully_checks_incomplete_price_collection(
        ProductValueInterface $value,
        ChannelInterface $channel,
        ArrayCollection $arrayCollection,
        CurrencyInterface $currency1,
        CurrencyInterface $currency2,
        ProductPriceInterface $price1
    ) {
        $channel->getCurrencies()->willReturn($arrayCollection);
        $arrayCollection->toArray()->willReturn([$currency1, $currency2]);

        $currency1->getCode()->willReturn('USD');

        $price1->getCurrency()->willReturn('USD');
        $price1->getData()->willReturn(null);

        $value->getData()->willReturn([$price1]);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);
    }
}
