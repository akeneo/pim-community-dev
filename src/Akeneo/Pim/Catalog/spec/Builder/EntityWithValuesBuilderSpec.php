<?php

namespace spec\Pim\Component\Catalog\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Prophecy\Argument;

class EntityWithValuesBuilderSpec extends ObjectBehavior
{
    public function let(
        AttributeValuesResolverInterface $valuesResolver,
        ValueFactory $productValueFactory
    ) {
        $this->beConstructedWith($valuesResolver, $productValueFactory);
    }

    function it_adds_an_empty_product_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size,
        AttributeInterface $color,
        ValueInterface $sizeValue,
        ValueInterface $colorValue
    ) {
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->isLocalizable()->willReturn(false);
        $size->isScopable()->willReturn(false);

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $productValueFactory->create($size, null, null, null)->willReturn($sizeValue);
        $productValueFactory->create($color, 'ecommerce', 'en_US', null)->willReturn($colorValue);

        $product->addValue($sizeValue)->willReturn($product);
        $product->addValue($colorValue)->willReturn($product);

        $this->addOrReplaceValue($product, $size, null, null, null);
        $this->addOrReplaceValue($product, $color, 'en_US', 'ecommerce', null);
    }

    function it_adds_a_non_empty_product_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size,
        AttributeInterface $color,
        ValueInterface $sizeValue,
        ValueInterface $colorValue
    ) {
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->isLocalizable()->willReturn(false);
        $size->isScopable()->willReturn(false);

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $productValueFactory->create($size, null, null, null)->willReturn($sizeValue);
        $productValueFactory->create($color, 'ecommerce', 'en_US', 'red')->willReturn($colorValue);

        $product->addValue($sizeValue)->willReturn($product);
        $product->addValue($colorValue)->willReturn($product);

        $this->addOrReplaceValue($product, $size, null, null, null);
        $this->addOrReplaceValue($product, $color, 'en_US', 'ecommerce', 'red');
    }

    function it_adds_a_product_value_if_there_was_not_a_previous_one(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $label,
        ValueInterface $value
    ) {
        $label->getCode()->willReturn('label');
        $label->getType()->willReturn(AttributeTypes::TEXT);
        $label->isLocalizable()->willReturn(false);
        $label->isScopable()->willReturn(false);

        $product->getValue('label', null, null)->willReturn(null);

        $product->removeValue(Argument::any())->shouldNotBeCalled();

        $productValueFactory->create($label, null, null, 'foobar')->willReturn($value);

        $product->addValue($value)->willReturn($product);

        $this->addOrReplaceValue($product, $label, null, null, 'foobar');
    }
}
