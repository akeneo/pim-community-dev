<?php

namespace spec\Pim\Component\Catalog\Value;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\PriceCollectionInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;

class PriceCollectionValueSpec extends ObjectBehavior
{
    function let(AttributeInterface $attribute, PriceCollectionInterface $priceCollection)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);
    }

    function it_returns_data($priceCollection)
    {
        $this->getData()->shouldBeAnInstanceOf(PriceCollectionInterface::class);
        $this->getData()->shouldReturn($priceCollection);
    }

    function it_returns_a_price(
        $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $priceUSD->getCurrency()->willReturn('USD');
        $priceEUR->getCurrency()->willReturn('EUR');

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);
        $pricesIterator->next()->shouldBeCalled();

        $this->getPrice('USD')->shouldReturn($priceUSD);
    }

    function it_formats_prices_as_strings_with_two_decimals(
        $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $priceUSD->getData()->willReturn(34);
        $priceUSD->getCurrency()->willReturn('USD');
        $priceEUR->getData()->willReturn(7658.78);
        $priceEUR->getCurrency()->willReturn('EUR');

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);
        $pricesIterator->next()->shouldBeCalled();

        $this->__toString()->shouldReturn('7658.78 EUR, 34.00 USD');

    }

    function it_formats_prices_as_strings_with_two_decimals_and_omits_price_without_amount(
        $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $priceUSD->getData()->willReturn(34);
        $priceUSD->getCurrency()->willReturn('USD');
        $priceEUR->getData()->willReturn(null);
        $priceEUR->getCurrency()->willReturn('EUR');

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);
        $pricesIterator->next()->shouldBeCalled();

        $this->__toString()->shouldReturn('34.00 USD');
    }

    function it_returns_true_if_there_is_data(
        $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $priceUSD->getData()->willReturn(34);
        $priceUSD->getCurrency()->willReturn('USD');
        $priceEUR->getData()->willReturn(null);
        $priceEUR->getCurrency()->willReturn('EUR');

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);
        $pricesIterator->next()->shouldBeCalled();

        $this->hasData()->shouldReturn(true);
    }

    function it_returns_false_if_there_is_no_data(
        $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $priceUSD->getData()->willReturn(null);
        $priceEUR->getData()->willReturn(null);

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);
        $pricesIterator->next()->shouldBeCalled();

        $this->hasData()->shouldReturn(false);
    }
}
