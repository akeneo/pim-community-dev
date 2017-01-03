<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class BooleanAttributeSetterSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($builder, $attrValidatorHelper, ['pim_catalog_boolean']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
    }

    function it_supports_boolean_attributes(
        AttributeInterface $booleanAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $booleanAttribute->getAttributeType()->willReturn('pim_catalog_boolean');
        $this->supportsAttribute($booleanAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_an_attribute_data(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $booleanValue
    ) {
        $product->getFamily()->willReturn(null);

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('displayable');
        $product->getValue('displayable', 'fr_FR', 'mobile')->willReturn($booleanValue);
        $booleanValue->setData(true)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, true, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_sets_attribute_data_boolean_value_to_a_product_value(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = true;

        $product1->getFamily()->willReturn(null);
        $product2->getFamily()->willReturn(null);
        $product3->getFamily()->willReturn(null);

        $attribute->getCode()->willReturn('attributeCode');
        $productValue->setData($data)->shouldBeCalled();

        $builder
            ->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product3, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }

    function it_does_not_set_attribute_data_boolean_when_values_are_similars(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $family->getAttributeCodes()->willReturn(['sku', 'is_color']);
        $product->getFamily()->willReturn($family);

        $attribute->getCode()->willReturn('is_color');
        $product->getValue('is_color', null, null)->willReturn(null);

        $builder->addProductValue()->shouldNotBeCalled();

        $this->setAttributeData($product, $attribute, false, ['locale' => null, 'scope' => null]);
    }

    function it_sets_attribute_when_new_value_is_different_from_product_value(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product,
        FamilyInterface $family,
        ProductValue $productValue
    ) {
        $family->getAttributeCodes()->willReturn(['sku', 'is_color']);
        $product->getFamily()->willReturn($family);

        $attribute->getCode()->willReturn('is_color');
        $product->getValue('is_color', null, null)->willReturn(null);

        $builder->addProductValue($product, $attribute, null, null)->willReturn($productValue);

        $this->setAttributeData($product, $attribute, true, ['locale' => null, 'scope' => null]);
    }
}
