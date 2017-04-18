<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer, CollectionFilterInterface $filter)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');

        $this->beConstructedWith($filter);
        $this->setSerializer($serializer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_flat_normalization_of_product(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'flat')->shouldBe(true);
    }

    function it_does_not_support_flat_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'flat')->shouldBe(false);
    }

    function it_normalizes_product(
        $filter,
        ProductInterface $product,
        AttributeInterface $skuAttribute,
        ProductValueInterface $sku,
        Collection $values,
        FamilyInterface $family,
        $serializer
    ) {
        $family->getCode()->willReturn('shoes');
        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getType()->willReturn('pim_catalog_identifier');
        $skuAttribute->isLocalizable()->willReturn(false);
        $skuAttribute->isScopable()->willReturn(false);
        $sku->getAttribute()->willReturn($skuAttribute);
        $sku->getData()->willReturn('sku-001');

        $product->getIdentifier()->willReturn($sku);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['group1', 'group2', 'variant_group_1']);
        $product->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $product->getAssociations()->willReturn([]);
        $product->getValues()->willReturn($values);
        $filter->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())->willReturn([$sku]);

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family'     => 'shoes',
                'groups'     => 'group1,group2,variant_group_1',
                'categories' => 'nice shoes,converse',
                'sku'        => 'sku-001',
                'enabled'    => 1,
            ]
        );
    }

    function it_normalizes_product_with_associations(
        $filter,
        ProductInterface $product,
        AttributeInterface $skuAttribute,
        ProductValueInterface $sku,
        Association $myCrossSell,
        AssociationTypeInterface $crossSell,
        Association $myUpSell,
        AssociationTypeInterface $upSell,
        GroupInterface $associatedGroup1,
        GroupInterface $associatedGroup2,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ProductValueInterface $skuAssocProduct1,
        ProductValueInterface $skuAssocProduct2,
        Collection $values,
        FamilyInterface $family,
        $serializer
    ) {
        $family->getCode()->willReturn('shoes');
        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getType()->willReturn('pim_catalog_identifier');
        $skuAttribute->isLocalizable()->willReturn(false);
        $skuAttribute->isScopable()->willReturn(false);
        $sku->getAttribute()->willReturn($skuAttribute);
        $sku->getData()->willReturn('sku-001');

        $crossSell->getCode()->willReturn('cross_sell');
        $myCrossSell->getAssociationType()->willReturn($crossSell);
        $myCrossSell->getGroups()->willReturn([]);
        $myCrossSell->getProducts()->willReturn([]);
        $upSell->getCode()->willReturn('up_sell');
        $myUpSell->getAssociationType()->willReturn($upSell);
        $associatedGroup1->getCode()->willReturn('associated_group1');
        $associatedGroup2->getCode()->willReturn('associated_group2');
        $myUpSell->getGroups()->willReturn([$associatedGroup1, $associatedGroup2]);
        $skuAssocProduct1->getAttribute()->willReturn($skuAttribute);
        $skuAssocProduct2->getAttribute()->willReturn($skuAttribute);
        $skuAssocProduct1->__toString()->willReturn('sku_assoc_product1');
        $skuAssocProduct2->__toString()->willReturn('sku_assoc_product2');
        $associatedProduct1->getIdentifier()->willReturn($skuAssocProduct1);
        $associatedProduct2->getIdentifier()->willReturn($skuAssocProduct2);
        $myUpSell->getProducts()->willReturn([$associatedProduct1, $associatedProduct2]);

        $product->getIdentifier()->willReturn($sku);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['group1,group2', 'variant_group_1']);
        $product->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $product->getAssociations()->willReturn([$myCrossSell, $myUpSell]);
        $product->getValues()->willReturn($values);
        $filter->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())->willReturn([$sku]);

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family'              => 'shoes',
                'groups'              => 'group1,group2,variant_group_1',
                'categories'          => 'nice shoes,converse',
                'cross_sell-groups'   => '',
                'cross_sell-products' => '',
                'up_sell-groups'      => 'associated_group1,associated_group2',
                'up_sell-products'    => 'sku_assoc_product1,sku_assoc_product2',
                'sku'                 => 'sku-001',
                'enabled'             => 1,
            ]
        );
    }

    function it_normalizes_product_with_a_multiselect_value(
        $filter,
        $serializer,
        ProductInterface $product,
        AttributeInterface $skuAttribute,
        AttributeInterface $colorsAttribute,
        ProductValueInterface $sku,
        ProductValueInterface $colors,
        AttributeOptionInterface $red,
        AttributeOptionInterface $blue,
        Collection $values,
        FamilyInterface $family
    ) {
        $family->getCode()->willReturn('shoes');
        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getType()->willReturn('pim_catalog_identifier');
        $skuAttribute->isLocalizable()->willReturn(false);
        $skuAttribute->isScopable()->willReturn(false);
        $sku->getAttribute()->willReturn($skuAttribute);
        $sku->getData()->willReturn('sku-001');

        $colorsAttribute->getCode()->willReturn('colors');
        $colorsAttribute->isLocalizable()->willReturn(false);
        $colorsAttribute->isScopable()->willReturn(false);
        $colors->getAttribute()->willReturn($colorsAttribute);
        $colors->getData()->willReturn([$red, $blue]);

        $product->getIdentifier()->willReturn($sku);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $product->getAssociations()->willReturn([]);
        $product->getValues()->willReturn($values);
        $filter
            ->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())
            ->willReturn([$sku, $colors]);

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);
        $serializer->normalize($colors, 'flat', Argument::any())->willReturn(['colors' => 'red, blue']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family'     => 'shoes',
                'groups'     => '',
                'categories' => '',
                'colors'     => 'red, blue',
                'sku'        => 'sku-001',
                'enabled'    => 1,
            ]
        );
    }

    function it_normalizes_product_with_price(
        $filter,
        ProductInterface $product,
        AttributeInterface $priceAttribute,
        ProductValueInterface $price,
        Collection $prices,
        Collection $values,
        ProductPriceInterface $productPrice,
        FamilyInterface $family,
        SerializerInterface $serializer
    ) {
        $family->getCode()->willReturn('shoes');
        $priceAttribute->getCode()->willReturn('price');
        $priceAttribute->getType()->willReturn('pim_catalog_price_collection');
        $priceAttribute->isLocalizable()->willReturn(false);
        $priceAttribute->isScopable()->willReturn(false);

        $price->getAttribute()->willReturn($priceAttribute);
        $price->getData()->willReturn(null);

        $productPrice->getData()->willReturn("356.00");
        $productPrice->getCurrency()->willReturn("EUR");

        $prices->add($productPrice);

        $price->getData()->willReturn($prices);

        $product->getIdentifier()->willReturn($price);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['group1', 'group2', 'variant_group_1']);
        $product->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $product->getAssociations()->willReturn([]);

        $values->add($price);

        $product->getValues()->willReturn($values);
        $filter->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())->willReturn(
            [$price]
        );

        $serializer->normalize($price, 'flat', Argument::any())->willReturn(['price-EUR' => '356.00']);

        $this->normalize($product, 'flat', ['price-EUR' => ''])->shouldReturn(
            [
                'family'     => 'shoes',
                'groups'     => 'group1,group2,variant_group_1',
                'categories' => 'nice shoes,converse',
                'price-EUR'  => '356.00',
                'enabled'    => 1,
            ]
        );
    }
}
