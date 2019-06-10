<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EntityWithValuesBuilderSpec extends ObjectBehavior
{
    function let(
        AttributeValuesResolverInterface $valuesResolver,
        ValueFactory $productValueFactory
    ) {
        $this->beConstructedWith($valuesResolver, $productValueFactory);
    }

    function it_adds_a_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size
    ) {
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->getCode()->willReturn('size');

        $product->getValue('size', null, null)->willReturn(null);
        $sizeValue = ScalarValue::value('size', 'XL');
        $productValueFactory->create($size, null, null, 'XL')->willReturn($sizeValue);

        $product->addOrReplaceValue($sizeValue)->shouldBeCalled();

        $this->addOrReplaceValue($product, $size, null, null, 'XL');
    }

    function it_removes_an_existing_value_if_data_is_empty(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size
    ) {
        $formerValue = ScalarValue::value('size', 'XL');

        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->getCode()->willReturn('size');

        $product->getValue('size', null, null)->willReturn($formerValue);
        $sizeValue = ScalarValue::value('size', 'XL');
        $productValueFactory->create($size, null, null, null)->willReturn($sizeValue);

        $product->removeValue($formerValue)->shouldBeCalled();
        $product->addOrReplaceValue(Argument::any())->shouldNotBeCalled();

        $this->addOrReplaceValue($product, $size, null, null, null);
    }

    function it_replaces_a_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $color
    ) {
        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);

        $formerColorValue = ScalarValue::scopableLocalizableValue('color', 'red', 'ecommerce', 'en_US');
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($formerColorValue);

        $newColorValue = ScalarValue::scopableLocalizableValue('color', 'blue', 'ecommerce', 'en_US');
        $productValueFactory->create($color, 'ecommerce', 'en_US', 'blue')->willReturn($newColorValue);

        $product->removeValue($formerColorValue)->shouldNotBeCalled();
        $product->addOrReplaceValue($newColorValue)->shouldBeCalled();

        $this->addOrReplaceValue($product, $color, 'en_US', 'ecommerce', 'blue');
    }
}
