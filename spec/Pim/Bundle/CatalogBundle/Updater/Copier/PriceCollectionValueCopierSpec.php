<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;

class PriceCollectionValueCopierSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder)
    {
        $this->beConstructedWith($builder, ['pim_catalog_price_collection']);
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface');
    }

    function it_supports_metric_attributes(
        AttributeInterface $fromPriceCollectionAttribute,
        AttributeInterface $toPriceCollectionAttribute,
        AttributeInterface $toTextareaAttribute,
        AttributeInterface $fromNumberAttribute,
        AttributeInterface $toNumberAttribute
    ) {
        $fromPriceCollectionAttribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $toPriceCollectionAttribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $this->supports($fromPriceCollectionAttribute, $toPriceCollectionAttribute)->shouldReturn(true);

        $fromNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toPriceCollectionAttribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $this->supports($fromNumberAttribute, $toNumberAttribute)->shouldReturn(false);

        $this->supports($fromPriceCollectionAttribute, $toNumberAttribute)->shouldReturn(false);
        $this->supports($fromNumberAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_a_price_collection_value_to_a_product_value(
        $builder,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        AbstractProduct $product1,
        AbstractProduct $product2,
        AbstractProduct $product3,
        AbstractProduct $product4,
        ProductValue $fromProductValue,
        ProductValue $toProductValue,
        ProductPriceInterface $price
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

        $fromProductValue->getData()->willReturn([$price]);

        $price->getCurrency()->willReturn('USD');
        $price->getData()->willReturn(123);


        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $product4->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product4->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $builder->addProductValue($product3, $toAttribute, $toLocale, $toScope)->shouldBeCalledTimes(1)->willReturn($toProductValue);

        $builder->addPriceForCurrencyWithData($toProductValue, 'USD', 123)->shouldBeCalled();

        $products = [$product1, $product2, $product3, $product4];

        $this->copyValue($products, $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);
    }
}
