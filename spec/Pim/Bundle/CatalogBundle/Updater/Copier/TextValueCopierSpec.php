<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;

class TextValueCopierSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder)
    {
        $this->beConstructedWith($builder);
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface');
    }

    function it_supports_text_attributes(
        AttributeInterface $fromTextAttribute,
        AttributeInterface $toTextAttribute,
        AttributeInterface $fromTextareaAttribute,
        AttributeInterface $toTextareaAttribute,
        AttributeInterface $fromNumberAttribute,
        AttributeInterface $toNumberAttribute
    ) {
        $fromTextAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $toTextAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $this->supports($fromTextAttribute, $toTextAttribute)->shouldReturn(true);

        $fromTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(true);

        $fromNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supports($fromNumberAttribute, $toNumberAttribute)->shouldReturn(false);

        $this->supports($fromTextAttribute, $toNumberAttribute)->shouldReturn(false);
        $this->supports($fromNumberAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copy_numeric_value_to_a_product_value(
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

        $fromProductValue->getData()->willReturn('data');
        $toProductValue->setData('data')->shouldBeCalledTimes(2);
        $toProductValue->setData('')->shouldBeCalledTimes(2);


        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $product4->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product4->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $builder->addProductValue($product3, $toAttribute, $toLocale, $toScope)->shouldBeCalledTimes(1)->willReturn($toProductValue);

        $products = [$product1, $product2, $product3, $product4];

        $this->copyValue($products, $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);
    }
}
