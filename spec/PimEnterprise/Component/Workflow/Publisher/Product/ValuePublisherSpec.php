<?php

namespace spec\PimEnterprise\Component\Workflow\Publisher\Product;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;
use Prophecy\Argument;

class ValuePublisherSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Workflow\Publisher\Product\ValuePublisher');
    }

    public function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Component\Workflow\Publisher\PublisherInterface');
    }

    public function let(PublisherInterface $publisher)
    {
        $this->beConstructedWith(
            'PimEnterprise\Component\Workflow\Model\PublishedProductValue',
            $publisher
        );
    }

    public function it_publishes_a_true_boolean_product_value(
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('boolean');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);

        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn('ecommerce');
        $productValue->getEntity()->willReturn(null);

        $productValue->getData()->willReturn(true);

        $published = $this->publish($productValue);

        $published->shouldHaveType('PimEnterprise\Component\Workflow\Model\PublishedProductValue');

        $published->getData()->shouldReturn(true);
        $published->getAttribute()->shouldReturn($attribute);
        $published->getLocale()->shouldReturn('en_US');
        $published->getScope()->shouldReturn('ecommerce');
    }

    public function it_publishes_a_false_boolean_product_value(
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('boolean');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);

        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn('ecommerce');
        $productValue->getEntity()->willReturn(null);

        $productValue->getData()->willReturn(false);

        $published = $this->publish($productValue);

        $published->shouldHaveType('PimEnterprise\Component\Workflow\Model\PublishedProductValue');

        $published->getData()->shouldReturn(false);
        $published->getAttribute()->shouldReturn($attribute);
        $published->getLocale()->shouldReturn('en_US');
        $published->getScope()->shouldReturn('ecommerce');
    }

    public function it_publishes_a_string_product_value(
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('varchar');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);
        $productValue->getEntity()->willReturn(null);

        $productValue->getData()->willReturn('string_product_value');

        $published = $this->publish($productValue);

        $published->shouldHaveType('PimEnterprise\Component\Workflow\Model\PublishedProductValue');

        $published->getData()->shouldReturn('string_product_value');
        $published->getAttribute()->shouldReturn($attribute);
        $published->getLocale()->shouldReturn(null);
        $published->getScope()->shouldReturn(null);
    }

    public function it_publishes_an_object_product_value(
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        $publisher
    ) {

        $attribute->getBackendType()->willReturn('boolean');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->isScopable()->willReturn(true);

        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn('ecommerce');
        $productValue->getEntity()->willReturn(null);

        $object = new \StdClass();

        $publisher->publish($object, ['product' => null, 'value' => $productValue])->willReturn($object);

        $productValue->getData()->willReturn($object);

        $published = $this->publish($productValue);

        $published->shouldHaveType('PimEnterprise\Component\Workflow\Model\PublishedProductValue');

        $published->getData()->shouldReturn($object);
        $published->getAttribute()->shouldReturn($attribute);
        $published->getLocale()->shouldReturn(null);
        $published->getScope()->shouldReturn('ecommerce');
    }

    public function it_publishes_a_price_product_value(
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ProductPriceInterface $price1,
        ProductPriceInterface $price2,
        $publisher
    ) {

        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);
        $productValue->getEntity()->willReturn(null);

        $price1->getCurrency()->willReturn('USD');
        $price2->getCurrency()->willReturn('EUR');

        $price1->setValue(Argument::any())->shouldBeCalled();
        $price2->setValue(Argument::any())->shouldBeCalled();

        $prices = new ArrayCollection();
        $prices->add($price1);
        $prices->add($price2);

        $publisher->publish(Argument::any(), Argument::any())->willReturn($price1, $price2);

        $productValue->getData()->willReturn($prices);

        $published = $this->publish($productValue);

        $published->shouldHaveType('PimEnterprise\Component\Workflow\Model\PublishedProductValue');

        $prices = $published->getData()->toArray();
        $prices->shouldReturn(['EUR' => $price2, 'USD' => $price1]);
        $published->getAttribute()->shouldReturn($attribute);
        $published->getLocale()->shouldReturn(null);
        $published->getScope()->shouldReturn(null);
    }
}
