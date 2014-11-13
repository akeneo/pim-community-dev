<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;

class NumberValueCopierSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder)
    {
        $this->beConstructedWith($builder, ['pim_catalog_number']);
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface');
    }

    function it_supports_number_attributes(
        AttributeInterface $fromTextAttribute,
        AttributeInterface $fromTextareaAttribute,
        AttributeInterface $fromIdentifierAttribute,
        AttributeInterface $toTextareaAttribute,
        AttributeInterface $fromNumberAttribute,
        AttributeInterface $toNumberAttribute
    ) {
        $fromNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supports($fromNumberAttribute, $toNumberAttribute)->shouldReturn(true);

        $fromTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromIdentifierAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $this->supports($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $this->supports($fromTextAttribute, $toNumberAttribute)->shouldReturn(false);
        $this->supports($fromNumberAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_number_value_to_a_product_value(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        AbstractProduct $product1,
        AbstractProduct $product2,
        AbstractProduct $product3,
        AbstractProduct $product4,
        ProductValue $fromProductValue,
        ProductValue $toProductValue,
        $builder
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $fromAttribute->isScopable()->shouldBeCalled()->willReturn(true);
        $fromAttribute->getCode()->willReturn('fromAttributeCode');

        $toAttribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $toAttribute->isScopable()->shouldBeCalled()->willReturn(true);
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $fromProductValue->getData()->willReturn(123);
        $toProductValue->setData(123)->shouldBeCalledTimes(3);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $product4->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product4->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $builder->addProductValue($product3, $toAttribute, $toLocale, $toScope)->shouldBeCalledTimes(1)->willReturn($toProductValue);

        $products = [$product1, $product2, $product3, $product4];

        $this->copyValue($products, $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);
    }
}
