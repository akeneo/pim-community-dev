<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use PhpSpec\ObjectBehavior;

class PriceCollectionValueSpec extends ObjectBehavior
{
    function it_returns_data(
        PriceCollectionInterface $priceCollection
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

        $this->getData()->shouldBeAnInstanceOf(PriceCollectionInterface::class);
        $this->getData()->shouldReturn($priceCollection);
    }

    function it_returns_a_price(
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

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
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

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
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

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
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

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
        PriceCollectionInterface $priceCollection,
        \ArrayIterator $pricesIterator,
        ProductPriceInterface $priceUSD,
        ProductPriceInterface $priceEUR
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

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
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

        $samePriceCollValue->getData()->willReturn($samePriceCollection);
        $samePriceCollValue->getLocaleCode()->willReturn('en_US');
        $samePriceCollValue->getScopeCode()->willReturn('ecommerce');

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
        PriceCollectionValueInterface $samePriceCollection
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', null, 'ecommerce', 'en_US']);

        $samePriceCollection->getData()->willReturn(null);
        $samePriceCollection->getLocaleCode()->willReturn('en_US');
        $samePriceCollection->getScopeCode()->willReturn('ecommerce');

        $this->isEqual($samePriceCollection)->shouldReturn(true);
    }

    function it_compares_itself_to_a_price_collection_value_with_null_collection(
        PriceCollectionValueInterface $samePriceCollection,
        PriceCollectionInterface $priceCollection
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

        $samePriceCollection->getData()->willReturn(null);
        $samePriceCollection->getLocaleCode()->willReturn('en_US');
        $samePriceCollection->getScopeCode()->willReturn('ecommerce');

        $this->isEqual($samePriceCollection)->shouldReturn(false);
    }

    function it_compares_itself_to_another_value_type(
        PriceCollectionInterface $priceCollection,
        MetricValueInterface $metricValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_price_collection_value(
        PriceCollectionInterface $priceCollection,
        PriceCollectionValueInterface $differentPriceCollValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

        $differentPriceCollValue->getLocaleCode()->willReturn('fr_FR');
        $differentPriceCollValue->getScopeCode()->willReturn('ecommerce');

        $this->isEqual($differentPriceCollValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_price_collection_value_with_different_collection_count(
        PriceCollectionInterface $priceCollection,
        PriceCollectionInterface $differentPriceCollection,
        PriceCollectionValueInterface $differentPriceCollValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

        $differentPriceCollValue->getData()->willReturn($differentPriceCollection);
        $differentPriceCollValue->getLocaleCode()->willReturn('en_US');
        $differentPriceCollValue->getScopeCode()->willReturn('ecommerce');

        $differentPriceCollection->count()->willReturn(1);
        $priceCollection->count()->willReturn(2);

        $this->isEqual($differentPriceCollValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_price_collection_value_with_different_price_collection(
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
        $this->beConstructedThrough('scopableLocalizableValue', ['my_prices', $priceCollection, 'ecommerce', 'en_US']);

        $samePriceCollValue->getData()->willReturn($samePriceCollection);
        $samePriceCollValue->getLocaleCode()->willReturn('en_US');
        $samePriceCollValue->getScopeCode()->willReturn('ecommerce');

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
