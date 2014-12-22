<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\TransformBundle\Normalizer\Filter\NormalizerFilterInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    public function let(SerializerInterface $serializer, NormalizerFilterInterface $filter)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
        $this->setFilters([$filter]);
    }

    public function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    public function it_supports_csv_normalization_of_product(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'csv')->shouldBe(true);
    }

    public function it_supports_flat_normalization_of_product(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'flat')->shouldBe(true);
    }

    public function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    public function it_normalizes_product(
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
        $skuAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $skuAttribute->isLocalizable()->willReturn(false);
        $skuAttribute->isScopable()->willReturn(false);
        $sku->getAttribute()->willReturn($skuAttribute);
        $sku->getData()->willReturn('sku-001');

        $product->getIdentifier()->willReturn($sku);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn('group1, group2, variant_group_1');
        $product->getCategoryCodes()->willReturn('nice shoes, converse');
        $product->getAssociations()->willReturn([]);
        $product->getValues()->willReturn($values);
        $filter->filter(Argument::cetera())->willReturn([$sku]);

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'sku'        => 'sku-001',
                'family'     => 'shoes',
                'groups'     => 'group1, group2, variant_group_1',
                'categories' => 'nice shoes, converse',
                'enabled'    => 1,
            ]
        );
    }

    public function it_normalizes_product_with_associations(
        $filter,
        ProductInterface $product,
        AttributeInterface $skuAttribute,
        ProductValueInterface $sku,
        Association $myCrossSell,
        AssociationType $crossSell,
        Association $myUpSell,
        AssociationType $upSell,
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
        $skuAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
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
        $product->getGroupCodes()->willReturn('group1,group2,variant_group_1');
        $product->getCategoryCodes()->willReturn('nice shoes, converse');
        $product->getAssociations()->willReturn([$myCrossSell, $myUpSell]);
        $product->getValues()->willReturn($values);
        $filter->filter(Argument::cetera())->willReturn([$sku]);

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'sku'        => 'sku-001',
                'family'     => 'shoes',
                'groups'     => 'group1,group2,variant_group_1',
                'categories' => 'nice shoes, converse',
                'cross_sell-groups' => '',
                'cross_sell-products' => '',
                'up_sell-groups' => 'associated_group1,associated_group2',
                'up_sell-products' => 'sku_assoc_product1,sku_assoc_product2',
                'enabled'    => 1,
            ]
        );
    }

    public function it_normalizes_product_with_a_multiselect_value(
        $filter,
        $serializer,
        ProductInterface $product,
        AttributeInterface $skuAttribute,
        AttributeInterface $colorsAttribute,
        ProductValueInterface $sku,
        ProductValueInterface $colors,
        AttributeOption $red,
        AttributeOption $blue,
        Collection $values,
        FamilyInterface $family
    ) {
        $family->getCode()->willReturn('shoes');
        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
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
        $product->getGroupCodes()->willReturn('');
        $product->getCategoryCodes()->willReturn('');
        $product->getAssociations()->willReturn([]);
        $product->getValues()->willReturn($values);
        $filter
            ->filter($values, ['identifier' => $sku, 'scopeCode' => null, 'localeCodes' => []])
            ->willReturn([$sku, $colors]);

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);
        $serializer->normalize($colors, 'flat', Argument::any())->willReturn(['colors' => 'red, blue']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'sku'        => 'sku-001',
                'family'     => 'shoes',
                'groups'     => '',
                'categories' => '',
                'colors'     => 'red, blue',
                'enabled'    => 1,
            ]
        );
    }

    public function it_normalizes_product_with_price(
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
        $priceAttribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $priceAttribute->isLocalizable()->willReturn(false);
        $priceAttribute->isScopable()->willReturn(false);

        $price->getAttribute()->willReturn($priceAttribute);
        $price->getData()->willReturn(null);

        $productPrice->getData()->willReturn("356.00");
        $productPrice->getCurrency()->willReturn("EUR");

        $prices->add($productPrice);

        $price->getPrices()->willReturn($prices);

        $product->getIdentifier()->willReturn($price);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn('group1, group2, variant_group_1');
        $product->getCategoryCodes()->willReturn('nice shoes, converse');
        $product->getAssociations()->willReturn([]);

        $values->add($price);

        $product->getValues()->willReturn($values);
        $filter->filter(Argument::cetera())->willReturn([$price]);

        $serializer->normalize($price, 'flat', Argument::any())->willReturn(['price-EUR' => '356.00']);

        $this->normalize($product, 'flat', ['price-EUR' => ''])->shouldReturn(
            [
                'price-EUR'        => '356.00',
                'family'     => 'shoes',
                'groups'     => 'group1, group2, variant_group_1',
                'categories' => 'nice shoes, converse',
                'enabled'    => 1,
            ]
        );
    }
}
