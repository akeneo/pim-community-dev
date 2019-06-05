<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Prophecy\Argument;

class EntityWithValuesBuilderSpec extends ObjectBehavior
{
    function let(
        AttributeValuesResolverInterface $valuesResolver,
        ValueFactory $productValueFactory
    ) {
        $this->beConstructedWith($valuesResolver, $productValueFactory);
    }

    function it_adds_a_product_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size
    ) {
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->isLocalizable()->willReturn(false);
        $size->isScopable()->willReturn(false);

        $product->getValue('size', null, null)->willReturn(null);
        $sizeValue = ScalarValue::value('size', 'XL');
        $productValueFactory->create($size, null, null, 'XL')->willReturn($sizeValue);

        $product->removeValue(Argument::any())->shouldNotBeCalled();
        $product->addValue($sizeValue)->shouldBeCalled();

        $this->addOrReplaceValue($product, $size, null, null, 'XL');
    }

    function it_replaces_a_product_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $color
    ) {
        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);

        $formerColorValue = ScalarValue::scopableLocalizableValue('color', 'red', 'ecommerce', 'en_US');
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($formerColorValue);

        $newColorValue = ScalarValue::scopableLocalizableValue('color', 'blue', 'ecommerce', 'en_US');
        $productValueFactory->create($color, 'ecommerce', 'en_US', 'blue')->willReturn($newColorValue);

        $product->removeValue($formerColorValue)->shouldBeCalled();
        $product->addValue($newColorValue)->shouldBeCalled();

        $this->addOrReplaceValue($product, $color, 'en_US', 'ecommerce', 'blue');
    }

    function it_does_not_replace_identical_values(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $label
    ) {
        $label->getCode()->willReturn('label');
        $label->getType()->willReturn(AttributeTypes::TEXT);
        $label->isLocalizable()->willReturn(false);
        $label->isScopable()->willReturn(false);

        $formerLabelValue = ScalarValue::value('label', 'A label');
        $product->getValue('label', null, null)->willReturn($formerLabelValue);

        $newLabelValue = ScalarValue::value('label', 'A label');
        $productValueFactory->create($label, null, null, 'A label')->willReturn($newLabelValue);

        $product->removeValue(Argument::any())->shouldNotBeCalled();
        $product->addValue(Argument::any())->shouldNotBeCalled();

        $this->addOrReplaceValue($product, $label, null, null, 'A label');
    }
}
