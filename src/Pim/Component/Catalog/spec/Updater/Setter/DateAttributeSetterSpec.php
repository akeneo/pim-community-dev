<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class DateAttributeSetterSpec extends ObjectBehavior
{
    function let(ProductBuilderInterface $builder, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith($builder, $attrValidatorHelper, ['pim_catalog_date']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface');
    }

    function it_supports_date_attributes(
        AttributeInterface $dateAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $dateAttribute->getAttributeType()->willReturn('pim_catalog_date');
        $this->supportsAttribute($dateAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_a_date_value(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $dateValue
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('release_date');
        $product->getValue('release_date', 'fr_FR', 'mobile')->willReturn($dateValue);
        $product->removeValue($dateValue)->shouldBeCalled()->willReturn($product);

        $this->setAttributeData($product, $attribute, '1970-01-01T00:00:00+01:00', ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_attribute_data_is_not_a_valid_date_format(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not a date';

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'attributeCode',
                'a string with the format yyyy-mm-dd',
                'setter',
                'date',
                gettype($data)
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_is_not_correctly_formatted(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = '1970-mm-01';

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'attributeCode',
                'a string with the format yyyy-mm-dd',
                'setter',
                'date',
                gettype($data)
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_allows_setting_attribute_data_to_null(
        ProductInterface $product,
        AttributeInterface $attribute,
        ProductValueInterface $value
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $product->getValue('attributeCode', null, null)->shouldBeCalled()->willReturn($value);

        $product->removeValue($value)->shouldBeCalled()->willReturn($product);

        $this->setAttributeData($product, $attribute, null, ['locale' => null, 'scope' => null]);
    }

    function it_throws_an_error_if_attribute_data_is_not_a_string_or_datetime_or_null(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 132654;

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'attributeCode',
                'datetime or string',
                gettype($data),
                'setter',
                'date'
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_sets_an_attribute_data_date_value_to_a_product_value_with_string(
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        $builder,
        ProductValueInterface $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = '1970-01-01T00:00:00+01:00';

        $attribute->getCode()->willReturn('attributeCode');

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product1->removeValue($productValue)->shouldBeCalled()->willReturn($product1);
        $builder
            ->addProductValue($product1, $attribute, $locale, $scope, new \DateTime($data))
            ->willReturn($productValue);

        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product2->removeValue($productValue)->shouldNotBeCalled();
        $builder
            ->addProductValue($product2, $attribute, $locale, $scope, new \DateTime($data))
            ->willReturn($productValue);

        $this->setAttributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setAttributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }
}
