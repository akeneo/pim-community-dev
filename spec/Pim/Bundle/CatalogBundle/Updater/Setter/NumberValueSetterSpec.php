<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;

class NumberValueSetterSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder)
    {
        $this->beConstructedWith($builder, ['pim_catalog_number']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_supports_number_attributes(
        AttributeInterface $numberAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $numberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supports($numberAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_returns_supported_attributes_types()
    {
        $this->getSupportedTypes()->shouldReturn(['pim_catalog_number']);
    }

    function it_throws_an_error_if_data_is_not_a_number(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not a number';

        $this->shouldThrow(
            InvalidArgumentException::numericExpected('attributeCode', 'setter', 'number')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_number_value_to_a_product_value(
        AttributeInterface $attribute,
        AbstractProduct $product1,
        AbstractProduct $product2,
        AbstractProduct $product3,
        $builder,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = 44;

        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
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
