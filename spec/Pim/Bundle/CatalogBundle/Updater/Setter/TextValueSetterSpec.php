<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;

class TextValueSetterSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder)
    {
        $this->beConstructedWith($builder);
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

    function it_throw_an_error_if_data_is_not_a_string(
        AttributeInterface $attribute
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = 42;

        $this->shouldThrow('\LogicException')->during('setValue', [[], $attribute, $data, $locale, $scope]);
    }

    function it_sets_text_value_to_a_product_value(
        AttributeInterface $attribute,
        AbstractProduct $product1,
        AbstractProduct $product2,
        AbstractProduct $product3,
        $builder,
        ProductValue $productValue
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = 'data';

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
