<?php

namespace spec\Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MultiSelectAttributeAdderSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        IdentifiableObjectRepositoryInterface $attrOptionRepository,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $attrOptionRepository,
            ['pim_catalog_multiselect']
        );
    }

    function it_is_a_adder()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Adder\AdderInterface');
    }

    function it_supports_multiselect_attributes(
        AttributeInterface $multiSelectAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $multiSelectAttribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supports($multiSelectAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_an_attribute_data(
        $attrValidatorHelper,
        $attrOptionRepository,
        AttributeInterface $attribute,
        ProductInterface $product,
        AttributeOptionInterface $red,
        ProductValueInterface $colorValue
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();
        $red->getCode()->willReturn('red');
        $attribute->getCode()->willReturn('color');
        $product->getValue('color', 'fr_FR', 'mobile')->willReturn($colorValue);

        $attrOptionRepository
            ->findOneByIdentifier('color.red')
            ->shouldBeCalledTimes(1)
            ->willReturn($red);

        $colorValue->getOptions()->willReturn([]);
        $colorValue->addOption($red)->shouldBeCalled();

        $this->addAttributeData($product, $attribute, ['red'], ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_attribute_data_is_not_an_array_of_option_codes(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['foo' => ['bar' => 'baz']];

        $this->shouldThrow(
            InvalidArgumentException::arrayStringValueExpected(
                'attributeCode',
                'foo',
                'adder',
                'multi select',
                'array'
            )
        )->during('addAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_an_option_code_is_unknown_on_attribute_data_set(
        $attrOptionRepository,
        ProductInterface $product,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['unknown code'];

        $attrOptionRepository
            ->findOneByIdentifier('attributeCode.unknown code')
            ->shouldBeCalledTimes(1)
            ->willReturn(null);

        $this->shouldThrow(
            InvalidArgumentException::arrayInvalidKey(
                'attributeCode',
                'code',
                'The option does not exist',
                'adder',
                'multi select',
                'unknown code'
            )
        )->during('addAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_adds_attribute_data_on_multiselect_value_to_a_product_value(
        $builder,
        $attrOptionRepository,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductValueInterface $productValue,
        AttributeOptionInterface $attributeOption
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attribute->getCode()->willReturn('attributeCode');

        $attributeOption->getCode()->willReturn('attributeOptionCode');

        $attrOptionRepository
            ->findOneByIdentifier('attributeCode.attributeOptionCode')
            ->shouldBeCalledTimes(3)
            ->willReturn($attributeOption);

        $productValue->addOption($attributeOption)->shouldBeCalled();

        $builder
            ->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);

        $this->addAttributeData($product1, $attribute, ['attributeOptionCode'], ['locale' => $locale, 'scope' => $scope]);
        $this->addAttributeData($product2, $attribute, ['attributeOptionCode'], ['locale' => $locale, 'scope' => $scope]);
        $this->addAttributeData($product3, $attribute, ['attributeOptionCode'], ['locale' => $locale, 'scope' => $scope]);
    }
}
