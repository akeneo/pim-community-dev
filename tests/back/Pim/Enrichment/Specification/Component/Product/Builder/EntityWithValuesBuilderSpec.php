<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
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
        ValueFactory $productValueFactory,
        GetAttributes $getAttributesQuery
    ) {
        $getAttributesQuery->forCode('size')->willReturn(
            new Attribute('size', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false, 'option', [])
        );
        $getAttributesQuery->forCode('color')->willReturn(
            new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], true, true, null, false, 'option', [])
        );
        $getAttributesQuery->forCode('label')->willReturn(
            new Attribute('label', AttributeTypes::TEXT, [], true, true, null, false, 'option', [])
        );


        $this->beConstructedWith($valuesResolver, $productValueFactory, $getAttributesQuery);
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
        $color->getCode()->willReturn('color');

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $sizeAttribute = new Attribute('size', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false, 'option', []);
        $colorAttribute = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], true, true, null, false, 'option', []);
        $productValueFactory->createByCheckingData($sizeAttribute, null, null, null)->willReturn($sizeValue);
        $productValueFactory->createByCheckingData($colorAttribute, 'ecommerce', 'en_US', null)->willReturn($colorValue);

        $product->addValue(Argument::any())->shouldNotBecalled();

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
        $color->getCode()->willReturn('color');

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $sizeAttribute = new Attribute('size', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false, 'option', []);
        $colorAttribute = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], true, true, null, false, 'option', []);

        $productValueFactory->createByCheckingData($sizeAttribute, null, null, null)->willReturn($sizeValue);
        $productValueFactory->createByCheckingData($colorAttribute, 'ecommerce', 'en_US', 'red')->willReturn($colorValue);

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

        $product->getValue('label', null, null)->willReturn(null);

        $product->removeValue(Argument::any())->shouldNotBeCalled();

        $labelAttribute = new Attribute('label', AttributeTypes::TEXT, [], true, true, null, false, 'option', []);
        $productValueFactory->createByCheckingData($labelAttribute, null, null, 'foobar')->willReturn($value);

        $product->addValue($value)->willReturn($product);

        $this->addOrReplaceValue($product, $label, null, null, 'foobar');
    }
}
