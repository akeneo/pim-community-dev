<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use PhpSpec\ObjectBehavior;

class PriceCollectionValueSpec extends ObjectBehavior
{
    function it_returns_data(
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

        $this->getData()->shouldBeAnInstanceOf(PriceCollectionInterface::class);
        $this->getData()->shouldReturn($priceCollection);
    }

    function it_returns_a_price(
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

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
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

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
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

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
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

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
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

        $priceUSD->getData()->willReturn(null);
        $priceEUR->getData()->willReturn(null);

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);
        $pricesIterator->next()->shouldBeCalled();

        $this->hasData()->shouldReturn(false);
    }

    function it_compares_itself_to_the_same_price_collection_value(
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        PriceCollectionValueInterface $samePriceCollValue,
        PriceCollectionInterface $samePriceCollection,
        \ArrayIterator $pricesIterator,
        \ArrayIterator $samePricesIterator,
        \ArrayIterator $samePricesIterator2,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR,
        ProductPriceInterface $samePriceUSD,
        ProductPriceInterface $samePriceEUR
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

        $samePriceCollValue->getData()->willReturn($samePriceCollection);
        $samePriceCollValue->getLocale()->willReturn('en_US');
        $samePriceCollValue->getScope()->willReturn('ecommerce');

        $priceCollection->count()->willReturn(2);
        $samePriceCollection->count()->willReturn(2);

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);

        $samePriceCollection->getIterator()->willReturn($samePricesIterator, $samePricesIterator2);
        $samePricesIterator->rewind()->shouldBeCalled();
        $samePricesIterator->valid()->willReturn(true, true, false);
        $samePricesIterator->current()->willReturn($samePriceEUR, $samePriceUSD);
        $samePricesIterator->next()->shouldBeCalled();

        $samePricesIterator2->valid()->willReturn(true, true, false);
        $samePricesIterator2->current()->willReturn($samePriceEUR, $samePriceUSD);

        $priceEUR->isEqual($samePriceEUR)->willReturn(true);
        $priceUSD->isEqual($samePriceEUR)->willReturn(false);
        $priceUSD->isEqual($samePriceUSD)->willReturn(true);

        $this->isEqual($samePriceCollValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_collection_to_a_price_collection_value_with_null_collection(
        AttributeInterface $attribute,
        PriceCollectionValueInterface $samePriceCollection
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $samePriceCollection->getData()->willReturn(null);
        $samePriceCollection->getLocale()->willReturn('en_US');
        $samePriceCollection->getScope()->willReturn('ecommerce');

        $this->isEqual($samePriceCollection)->shouldReturn(true);
    }

    function it_compares_itself_to_a_price_collection_value_with_null_collection(
        AttributeInterface $attribute,
        PriceCollectionValueInterface $samePriceCollection,
        PriceCollectionInterface $priceCollection
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

        $samePriceCollection->getData()->willReturn(null);
        $samePriceCollection->getLocale()->willReturn('en_US');
        $samePriceCollection->getScope()->willReturn('ecommerce');

        $this->isEqual($samePriceCollection)->shouldReturn(false);
    }

    function it_compares_itself_to_another_value_type(
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        MetricValueInterface $metricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_price_collection_value(
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        PriceCollectionValueInterface $differentPriceCollValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

        $differentPriceCollValue->getLocale()->willReturn('fr_FR');
        $differentPriceCollValue->getScope()->willReturn('ecommerce');

        $this->isEqual($differentPriceCollValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_price_collection_value_with_different_collection_count(
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        PriceCollectionInterface $differentPriceCollection,
        PriceCollectionValueInterface $differentPriceCollValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

        $differentPriceCollValue->getData()->willReturn($differentPriceCollection);
        $differentPriceCollValue->getLocale()->willReturn('en_US');
        $differentPriceCollValue->getScope()->willReturn('ecommerce');

        $differentPriceCollection->count()->willReturn(1);
        $priceCollection->count()->willReturn(2);

        $this->isEqual($differentPriceCollValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_price_collection_value_with_different_price_collection(
        AttributeInterface $attribute,
        PriceCollectionInterface $priceCollection,
        PriceCollectionValueInterface $samePriceCollValue,
        PriceCollectionInterface $samePriceCollection,
        \ArrayIterator $pricesIterator,
        \ArrayIterator $differentPricesIterator,
        \ArrayIterator $differentPricesIterator2,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR,
        ProductPriceInterface $differentPriceUSD,
        ProductPriceInterface $samePriceEUR
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $priceCollection);

        $samePriceCollValue->getData()->willReturn($samePriceCollection);
        $samePriceCollValue->getLocale()->willReturn('en_US');
        $samePriceCollValue->getScope()->willReturn('ecommerce');

        $priceCollection->count()->willReturn(2);
        $samePriceCollection->count()->willReturn(2);

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);
        $pricesIterator->next()->shouldBeCalled();

        $samePriceCollection->getIterator()->willReturn($differentPricesIterator, $differentPricesIterator2);
        $differentPricesIterator->rewind()->shouldBeCalled();
        $differentPricesIterator->valid()->willReturn(true, true, false);
        $differentPricesIterator->current()->willReturn($samePriceEUR, $differentPriceUSD);
        $differentPricesIterator->next()->shouldBeCalled();

        $differentPricesIterator2->valid()->willReturn(true, true, false);
        $differentPricesIterator2->current()->willReturn($samePriceEUR, $differentPriceUSD);

        $priceEUR->isEqual($samePriceEUR)->willReturn(true);
        $priceUSD->isEqual($samePriceEUR)->willReturn(false);
        $priceUSD->isEqual($differentPriceUSD)->willReturn(false);

        $this->isEqual($samePriceCollValue)->shouldReturn(false);
    }
}
