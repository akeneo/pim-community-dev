<?php

namespace spec\Pim\Component\Catalog\EmptyChecker\ProductValue;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class BaseEmptyCheckerSpec extends ObjectBehavior
{
    function it_is_a_product_value_empty_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\EmptyChecker\ProductValue\EmptyCheckerInterface');
    }

    function it_supports_all_native_attributes(ProductValueInterface $whateverValue)
    {
        $this->supports($whateverValue)->shouldReturn(true);
    }

    function it_checks_empty_text(
        ProductValueInterface $nullValue,
        ProductValueInterface $emptyStringValue,
        AttributeInterface $attribute
    ) {
        $nullValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::TEXT);
        $nullValue->getData()->willReturn(null);
        $this->isEmpty($nullValue)->shouldReturn(true);

        $emptyStringValue->getAttribute()->willReturn($attribute);
        $emptyStringValue->getData()->willReturn('');
        $this->isEmpty($emptyStringValue)->shouldReturn(true);
    }

    function it_checks_not_empty_text(ProductValueInterface $value, AttributeInterface $attribute)
    {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::TEXT);
        $value->getData()->willReturn('patapouet');
        $this->isEmpty($value)->shouldReturn(false);
    }

    function it_checks_empty_price_collection(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $value->getData()->willReturn([]);
        $this->isEmpty($value)->shouldReturn(true);
    }

    function it_checks_price_collection_with_empty_prices(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        ProductPriceInterface $priceEur,
        ProductPriceInterface $priceUsd
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $value->getData()->willReturn(
            [
                $priceEur,
                $priceUsd
            ]
        );
        $priceEur->getCurrency()->willReturn('EUR');
        $priceEur->getData()->willReturn(null);
        $priceUsd->getCurrency()->willReturn('USD');
        $priceUsd->getData()->willReturn(null);

        $this->isEmpty($value)->shouldReturn(true);
    }

    function it_checks_price_collection_with_a_not_empty_price(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        ProductPriceInterface $priceEur,
        ProductPriceInterface $priceUsd
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $value->getData()->willReturn(
            [
                $priceEur,
                $priceUsd
            ]
        );
        $priceEur->getCurrency()->willReturn('EUR');
        $priceEur->getData()->willReturn(null);
        $priceUsd->getCurrency()->willReturn('USD');
        $priceUsd->getData()->willReturn(12.45);

        $this->isEmpty($value)->shouldReturn(false);
    }

    function it_checks_empty_metric(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::METRIC);
        $value->getData()->willReturn($metric);
        $metric->getData()->willReturn(null);
        $this->isEmpty($value)->shouldReturn(true);
    }

    function it_checks_not_empty_metric(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        MetricInterface $metric
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::METRIC);
        $value->getData()->willReturn($metric);
        $metric->getData()->willReturn(23);
        $this->isEmpty($value)->shouldReturn(false);
    }

    function it_checks_empty_multi_select(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        Collection $options
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);
        $value->getData()->willReturn($options);
        $options->isEmpty()->willReturn(true);
        $this->isEmpty($value)->shouldReturn(true);
    }

    function it_checks_not_empty_multi_select(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        Collection $options
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);
        $value->getData()->willReturn($options);
        $options->isEmpty()->willReturn(false);
        $this->isEmpty($value)->shouldReturn(false);
    }

    function it_checks_empty_simple_select(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $value->getData()->willReturn(null);
        $this->isEmpty($value)->shouldReturn(true);
    }

    function it_checks_not_empty_simple_select(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        AttributeOptionInterface $option
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $value->getData()->willReturn($option);
        $this->isEmpty($value)->shouldReturn(false);
    }
}
