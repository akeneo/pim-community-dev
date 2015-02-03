<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class TextValueSetterSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($builder, $attrValidatorHelper, ['pim_catalog_text', 'pim_catalog_textarea']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_supports_text_attributes(
        AttributeInterface $textAttribute,
        AttributeInterface $textareaAttribute,
        AttributeInterface $numberAttribute
    ) {
        $textAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $this->supports($textAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(true);

        $numberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supports($numberAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_a_value(
        $attrValidatorHelper,
        AttributeInterface $attribute
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $this->setValue([], $attribute, 'data', 'fr_FR', 'mobile');
    }

    function it_throws_an_error_if_data_is_not_a_string(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 42;

        $this->shouldThrow(
            InvalidArgumentException::stringExpected('attributeCode', 'setter', 'text', gettype($data))
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_text_value_to_a_product_value(
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
            ->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->willReturn($productValue);

        $products = [$product1, $product2, $product3];

        $this->setValue($products, $attribute, $data, $locale, $scope);
    }
}
