<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class SimpleSelectAttributeSetterSpec extends ObjectBehavior
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
            ['pim_catalog_simpleselect']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
    }

    function it_supports_simpleselect_attributes(
        AttributeInterface $simpleSelectAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $simpleSelectAttribute->getType()->willReturn('pim_catalog_simpleselect');
        $this->supportsAttribute($simpleSelectAttribute)->shouldReturn(true);

        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_an_attribute_data(
        $attrValidatorHelper,
        $attrOptionRepository,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $optionValue,
        AttributeOptionInterface $attributeOption
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attributeOption->getCode()->willReturn('red');
        $attribute->getCode()->willReturn('color');
        $attrOptionRepository
            ->findOneByIdentifier('color.red')
            ->shouldBeCalledTimes(1)
            ->willReturn($attributeOption);

        $product->getValue('color', 'fr_FR', 'mobile')->willReturn($optionValue);
        $optionValue->getOption()->willReturn($attributeOption);
        $optionValue->setOption($attributeOption)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, 'red', ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_attribute_data_is_not_a_string_or_null(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['some', 'random', 'stuff'];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::stringExpected(
                    'attributeCode',
                    'Pim\Component\Catalog\Updater\Setter\SimpleSelectAttributeSetter',
                    $data
                )
            )
            ->duringSetAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_does_not_throw_an_error_if_attribute_is_a_numeric(
        $attrOptionRepository,
        AttributeInterface $attribute,
        ProductInterface $product,
        AttributeOptionInterface $attributeOption
    ) {
        $data = 70;

        $attrOptionRepository
            ->findOneByIdentifier('attributeCode.red')
            ->willReturn($attributeOption);

        $this
            ->shouldNotThrow(
                InvalidPropertyTypeException::stringExpected(
                    'attributeCode',
                    'Pim\Component\Catalog\Updater\Setter\SimpleSelectAttributeSetter',
                    $data
                )
            )
            ->duringSetAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_the_attribute_data_option_does_not_exist(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'unknown code';

        $this
            ->shouldThrow(
                InvalidPropertyException::validEntityCodeExpected(
                    'attributeCode',
                    'code',
                    'The option does not exist',
                    'Pim\Component\Catalog\Updater\Setter\SimpleSelectAttributeSetter',
                    'unknown code'
                )
            )
            ->duringSetAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_sets_attribute_data_simpleselect_option_to_a_product_value(
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

        $attributeOption->getCode()->willReturn('red');

        $attrOptionRepository
            ->findOneByIdentifier('attributeCode.red')
            ->shouldBeCalledTimes(3)
            ->willReturn($attributeOption);

        $productValue->setOption($attributeOption)->shouldBeCalled();

        $builder
            ->addOrReplaceProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, 'red', ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, 'red', ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product3, $attribute, 'red', ['locale' => $locale, 'scope' => $scope]);
    }

    function it_allows_setting_attribute_data_option_to_null(
        ProductInterface $product,
        AttributeInterface $attribute,
        ProductValueInterface $value
    ) {
        $attribute->getCode()->willReturn('choice');

        $product->getValue('choice', 'fr_FR', 'mobile')->shouldBeCalled()->willReturn($value);

        $value->setOption(null)->shouldBeCalled();

        $this->setAttributeData($product, $attribute, null, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }
}
