<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\CurrencyInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class PriceCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_suports_price_collection_attribute(
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $this->supportsValue($productValue)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn('other');
        $this->supportsValue($productValue)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_price_collection(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
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
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }

    public function it_succesfully_checks_incomplete_price_collection(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
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
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }
}
