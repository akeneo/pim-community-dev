<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class TextAttributeSetterSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($builder, $attrValidatorHelper, ['pim_catalog_text', 'pim_catalog_textarea']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
    }

    function it_supports_text_attributes(
        AttributeInterface $textAttribute,
        AttributeInterface $textareaAttribute,
        AttributeInterface $numberAttribute
    ) {
        $textAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $this->supportsAttribute($textAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(true);

        $numberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supportsAttribute($numberAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_an_attribute_data(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $textValue
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('description');
        $product->getValue('description', 'fr_FR', 'mobile')->willReturn($textValue);
        $textValue->setData('data');

        $this->setAttributeData($product, $attribute, 'data', ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_sets_attribute_data_text_value_to_a_product_value(
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        $builder,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = 'data';

        $attribute->getCode()->willReturn('attributeCode');
        $productValue->setData($data)->shouldBeCalled();

        $builder
            ->addOrReplaceProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product3, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }

    function it_sets_null_value_when_receiving_empty_string(
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        $builder,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = '';

        $attribute->getCode()->willReturn('attributeCode');
        $productValue->setData(null)->shouldBeCalled();

        $builder
            ->addOrReplaceProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }

    function it_throws_an_exception_when_locale_is_expected(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects a locale, none given.');
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(true);
        $attrValidatorHelper->validateLocale($attribute, null)->willThrow($e);
        $this->shouldThrow(
           InvalidPropertyException::expectedFromPreviousException(
               'attributeCode',
               'Pim\Component\Catalog\Updater\Setter\TextAttributeSetter',
               $e
           )
        )->during('setAttributeData', [$product, $attribute, '', ['locale' => null, 'scope' => 'ecommerce']]);
    }

    function it_throws_an_exception_when_locale_is_not_expected(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" does not expect a locale, "en_US" given.');
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(false);
        $attrValidatorHelper->validateLocale($attribute, 'en_US')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Setter\TextAttributeSetter',
                $e
            )
        )->during('setAttributeData', [$product, $attribute, '', ['locale' => 'en_US', 'scope' => 'ecommerce']]);
    }

    function it_throws_an_exception_when_locale_is_expected_but_not_activated(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects an existing and activated locale, "uz-UZ" given.');
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(true);
        $attrValidatorHelper->validateLocale($attribute, 'uz-UZ')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Setter\TextAttributeSetter',
                $e
            )
        )->during('setAttributeData', [$product, $attribute, '', ['locale' => 'uz-UZ', 'scope' => 'ecommerce']]);
    }

    function it_throws_an_exception_when_scope_is_expected(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects a scope, none given.');
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attrValidatorHelper->validateLocale($attribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, null)->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Setter\TextAttributeSetter',
                $e
            )
        )->during('setAttributeData', [$product, $attribute, '', ['locale' => null, 'scope' => null]]);
    }

    function it_throws_an_exception_when_scope_is_not_expected(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" does not expect a scope, "ecommerce" given.');
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attrValidatorHelper->validateLocale($attribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, 'ecommerce')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Setter\TextAttributeSetter',
                $e
            )
        )->during('setAttributeData', [$product, $attribute, '', ['locale' => null, 'scope' => 'ecommerce']]);
    }

    function it_throws_an_exception_when_scope_is_expected_but_not_existing(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects an existing scope, "ecommerce" given.');
        $attribute->getCode()->willReturn('attributeCode');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attrValidatorHelper->validateLocale($attribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, 'ecommerce')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Setter\TextAttributeSetter',
                $e
            )
        )->during('setAttributeData', [$product, $attribute, '', ['locale' => null, 'scope' => 'ecommerce']]);
    }

    function it_throws_an_exception_when_data_is_not_a_string(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');
        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Setter\TextAttributeSetter',
                []
            )
        )->during('setAttributeData', [$product, $attribute, [], ['locale' => null, 'scope' => 'ecommerce']]);
    }
}
