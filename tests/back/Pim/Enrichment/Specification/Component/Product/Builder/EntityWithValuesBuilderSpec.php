<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
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
        $size->getProperties()->willReturn([]);
        $size->isDecimalsAllowed()->willReturn(false);
        $size->getMetricFamily()->willReturn('');

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);
        $color->getProperties()->willReturn([]);
        $color->isDecimalsAllowed()->willReturn(false);
        $color->getMetricFamily()->willReturn('');

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $sizePublicApiAttribute = new Attribute('size', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false);
        $colorPublicApiAttribute = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], true, true, null, false);
        $productValueFactory->createByCheckingData($sizePublicApiAttribute, null, null, null)->willReturn($sizeValue);
        $productValueFactory->createByCheckingData($colorPublicApiAttribute, 'ecommerce', 'en_US', null)->willReturn($colorValue);

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
        $size->getProperties()->willReturn([]);
        $size->isDecimalsAllowed()->willReturn(false);
        $size->getMetricFamily()->willReturn('');

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);
        $color->getProperties()->willReturn([]);
        $color->isDecimalsAllowed()->willReturn(false);
        $color->getMetricFamily()->willReturn('');

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $sizePublicApiAttribute = new Attribute('size', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false);
        $colorPublicApiAttribute = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], true, true, null, false);
        $productValueFactory->createByCheckingData($sizePublicApiAttribute, null, null, null)->willReturn($sizeValue);
        $productValueFactory->createByCheckingData($colorPublicApiAttribute, 'ecommerce', 'en_US', 'red')->willReturn($colorValue);

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
        $label->getProperties()->willReturn([]);
        $label->isDecimalsAllowed()->willReturn(false);
        $label->getMetricFamily()->willReturn('');

        $product->getValue('label', null, null)->willReturn(null);

        $product->removeValue(Argument::any())->shouldNotBeCalled();

        $labelPublicApiAttribute = new Attribute('label', AttributeTypes::TEXT, [], false, false, null, false);
        $productValueFactory->createByCheckingData($labelPublicApiAttribute, null, null, 'foobar')->willReturn($value);

        $product->addValue($value)->willReturn($product);

        $this->addOrReplaceValue($product, $label, null, null, 'foobar');
    }
}
