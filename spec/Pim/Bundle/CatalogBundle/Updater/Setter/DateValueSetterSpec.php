<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Prophecy\Argument;

class DateValueSetterSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder)
    {
        $this->beConstructedWith($builder, ['pim_catalog_date']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_supports_date_attributes(
        AttributeInterface $dateAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $dateAttribute->getAttributeType()->willReturn('pim_catalog_date');
        $this->supports($dateAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_returns_supported_attributes_types()
    {
        $this->getSupportedTypes()->shouldReturn(['pim_catalog_date']);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not a date';

        $this->shouldThrow(
            InvalidArgumentException::expected('attributeCode', 'a string with the format yyyy-mm-dd', 'setter', 'date')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_data_is_not_correctly_formatted(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = '1970-mm-01';

        $this->shouldThrow(
            InvalidArgumentException::expected('attributeCode', 'a string with the format yyyy-mm-dd', 'setter', 'date')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_throws_an_error_if_data_is_not_a_string(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = new \Datetime();

        $this->shouldThrow(
            InvalidArgumentException::stringExpected('attributeCode', 'setter', 'date')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_date_value_to_a_product_value(
        AttributeInterface $attribute,
        AbstractProduct $product1,
        AbstractProduct $product2,
        AbstractProduct $product3,
        $builder,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = '1970-01-01';

        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');
        $productValue->setData(Argument::type('\Datetime'))->shouldBeCalled();

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
