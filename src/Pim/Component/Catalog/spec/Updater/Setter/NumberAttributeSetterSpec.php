<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class NumberAttributeSetterSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($builder, $attrValidatorHelper, ['pim_catalog_number']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
    }

    function it_supports_number_attributes(
        AttributeInterface $numberAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $numberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supportsAttribute($numberAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_an_attribute_data(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $numberValue
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('response_to_universe');
        $product->getValue('response_to_universe', 'fr_FR', 'mobile')->willReturn($numberValue);
        $product->removeValue($numberValue)->shouldBeCalled()->willReturn($product);

        $this->setAttributeData($product, $attribute, 42, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_sets_number_value_to_a_product_attribute_data_value(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = 44;

        $attribute->getCode()->willReturn('attributeCode');

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product1->removeValue($productValue)->shouldBeCalled()->willReturn($product1);
        $builder
            ->addProductValue($product1, $attribute, $locale, $scope, $data)
            ->willReturn($productValue);

        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product2->removeValue(null)->shouldNotBeCalled();
        $builder
            ->addProductValue($product2, $attribute, $locale, $scope, $data)
            ->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }

    function it_sets_non_number_value_to_a_product_attribute_data_value(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = 'foo';

        $attribute->getCode()->willReturn('attributeCode');

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product1->removeValue($productValue)->shouldBeCalled()->willReturn($product1);
        $builder
            ->addProductValue($product1, $attribute, $locale, $scope, $data)
            ->willReturn($productValue);

        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product2->removeValue(null)->shouldNotBeCalled();
        $builder
            ->addProductValue($product2, $attribute, $locale, $scope, $data)
            ->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }
}
