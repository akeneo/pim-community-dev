<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverterRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\HeterogeneousAttributeCopier;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HeterogeneousAttributeCopierSpec extends ObjectBehavior
{
    function let(
        ValueDataConverter $valueDataConverter,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $converterRegistry = new ValueDataConverterRegistry([$valueDataConverter->getWrappedObject()]);

        $this->beConstructedWith($converterRegistry, $entityWithValuesBuilder, $attrValidatorHelper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HeterogeneousAttributeCopier::class);
    }

    function it_does_not_support_attributes_with_the_same_type(
        AttributeInterface $name,
        AttributeInterface $brand
    ) {
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $brand->getType()->willReturn(AttributeTypes::TEXT);

        $this->supportsAttributes($name, $brand)->shouldReturn(false);
    }

    function it_supports_attributes_for_which_it_can_find_a_converter(
        ValueDataConverter $valueDataConverter,
        AttributeInterface $name,
        AttributeInterface $color,
        AttributeInterface $weight
    ) {
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $valueDataConverter->supportsAttributes($name, $color)->willReturn(true);

        $this->supportsAttributes($name, $color)->shouldReturn(true);

        $weight->getType()->willReturn(AttributeTypes::METRIC);
        $valueDataConverter->supportsAttributes($name, $weight)->willReturn(false);

        $this->supportsAttributes($name, $weight)->shouldReturn(false);
    }

    function it_copies_null_data_if_source_value_is_empty(
        ValueDataConverter $valueDataConverter,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $brand = (new Attribute())->setCode('brand');
        $brandSelect = (new Attribute())->setCode('brandSelect');

        $product = new Product();

        $valueDataConverter->supportsAttributes($brand, $brandSelect)->willReturn(true);
        $valueDataConverter->convert(Argument::cetera())->shouldNotBeCalled();

        $attrValidatorHelper->validateLocale($brand, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($brand, null)->shouldBeCalled();
        $attrValidatorHelper->validateLocale($brandSelect, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($brandSelect, null)->shouldBeCalled();

        $entityWithValuesBuilder->addOrReplaceValue($product, $brandSelect, null, null, null)->shouldBeCalled();

        $this->copyAttributeData(
            $product,
            $product,
            $brand,
            $brandSelect,
            []
        );
    }

    function it_copies_data_to_a_different_attribute_type(
        ValueDataConverter $valueDataConverter,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $brand = (new Attribute())->setCode('brand');
        $brandSelect = (new Attribute())->setCode('brandSelect');

        $brandValue = ScalarValue::scopableValue('brand', 'blue', 'ecommerce');
        $product = (new Product())->setValues(new WriteValueCollection([$brandValue]));

        $valueDataConverter->supportsAttributes($brand, $brandSelect)->willReturn(true);
        $valueDataConverter->convert($brandValue, $brandSelect)->willReturn('blue');

        $attrValidatorHelper->validateLocale($brand, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($brand, 'ecommerce')->shouldBeCalled();
        $attrValidatorHelper->validateLocale($brandSelect, 'en_US')->shouldBeCalled();
        $attrValidatorHelper->validateScope($brandSelect, null)->shouldBeCalled();

        $entityWithValuesBuilder->addOrReplaceValue($product, $brandSelect, 'en_US', null, 'blue')->shouldBeCalled();

        $this->copyAttributeData($product, $product, $brand, $brandSelect, [
            'from_scope' => 'ecommerce',
            'to_locale' => 'en_US',
        ]);
    }
}
