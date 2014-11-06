<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;

class SimpleSelectValueSetterSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder)
    {
        $this->beConstructedWith($builder);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
    }

    function it_supports_simpleselect_attributes(
        AttributeInterface $simpleSelectAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $simpleSelectAttribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $this->supports($simpleSelectAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($textareaAttribute)->shouldReturn(false);
    }

    function it_throws_an_error_if_data_is_not_a_simple_select_option(
        AttributeInterface $attribute
    ) {
        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not a simple select option';

        $this->shouldThrow(
            new \LogicException('Attribute "attributeCode" expects a simple select option as data')
        )->during('setValue', [[], $attribute, $data, 'fr_FR', 'mobile']);
    }

    function it_sets_simpleselect_value_to_a_product_value(
        AttributeInterface $attribute,
        AbstractProduct $product1,
        AbstractProduct $product2,
        AbstractProduct $product3,
        $builder,
        ProductValue $productValue,
        AttributeOption $attributeOption
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';

        $attribute->isLocalizable()->shouldBeCalled()->willReturn(true);
        $attribute->isScopable()->shouldBeCalled()->willReturn(true);
        $attribute->getCode()->willReturn('attributeCode');



        $data = $attributeOption;
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
