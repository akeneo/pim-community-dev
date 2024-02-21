<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\EntityWithQuantifiedAssociations\QuantifiedAssociationsNormalizer;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        CollectionFilterInterface $filter,
        QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer
    ) {
        $this->beConstructedWith($quantifiedAssociationsNormalizer, $filter);
        $this->setNormalizer($normalizer);
    }

    function it_is_a_normalizer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerInterface::class);
        $this->shouldImplement(NormalizerAwareInterface::class);
    }

    function it_supports_flat_normalization_of_product(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'flat')->shouldBe(true);
    }

    function it_does_not_support_flat_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'flat')->shouldBe(false);
    }

    function it_normalizes_variant_product(
        $filter,
        NormalizerInterface $normalizer,
        ProductInterface $product,
        ValueInterface $sku,
        WriteValueCollection $values,
        FamilyInterface $family,
        ProductModelInterface $parent,
        QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer
    ) {
        $family->getCode()->willReturn('shoes');

        $sku->getAttributeCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $sku->getData()->willReturn('sku-001');

        $product->isVariant()->willReturn(true);
        $product->getIdentifier()->willReturn($sku);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['group1', 'group2', 'group_3']);
        $product->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $product->getAssociations()->willReturn([]);
        $product->getValuesForVariation()->willReturn($values);
        $product->getParent()->willReturn($parent);
        $parent->getCode()->willReturn('parent_code');
        $filter->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())->willReturn([$sku]);

        $quantifiedAssociationsNormalizer->normalize($product, 'flat', Argument::any())->shouldBeCalled()->willReturn([]);
        $normalizer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family'     => 'shoes',
                'groups'     => 'group1,group2,group_3',
                'categories' => 'nice shoes,converse',
                'parent'     => 'parent_code',
                'sku'        => 'sku-001',
                'enabled'    => 1,
            ]
        );
    }

    function it_normalizes_product(
        $filter,
        ProductInterface $product,
        ValueInterface $sku,
        WriteValueCollection $values,
        FamilyInterface $family,
        QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer,
        NormalizerInterface $normalizer
    ) {
        $family->getCode()->willReturn('shoes');

        $sku->getAttributeCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $sku->getData()->willReturn('sku-001');

        $product->isVariant()->willReturn(false);
        $product->getIdentifier()->willReturn($sku);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['group1', 'group2', 'group_3']);
        $product->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $product->getAssociations()->willReturn([]);
        $product->getValues()->willReturn($values);
        $product->getParent()->willReturn(null);
        $filter->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())->willReturn([$sku]);

        $quantifiedAssociationsNormalizer->normalize($product, 'flat', Argument::any())->shouldBeCalled()->willReturn([]);
        $normalizer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family'     => 'shoes',
                'groups'     => 'group1,group2,group_3',
                'categories' => 'nice shoes,converse',
                'parent'     => '',
                'sku'        => 'sku-001',
                'enabled'    => 1,
            ]
        );
    }

    function it_normalizes_product_with_associations(
        $filter,
        ProductInterface $product,
        ValueInterface $sku,
        ProductAssociation $myCrossSell,
        AssociationTypeInterface $crossSell,
        ProductAssociation $myUpSell,
        AssociationTypeInterface $upSell,
        QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer,
        GroupInterface $associatedGroup1,
        GroupInterface $associatedGroup2,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ValueInterface $skuAssocProduct1,
        ValueInterface $skuAssocProduct2,
        WriteValueCollection $values,
        FamilyInterface $family,
        NormalizerInterface $normalizer
    ) {
        $family->getCode()->willReturn('shoes');

        $sku->getAttributeCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $sku->getData()->willReturn('sku-001');

        $crossSell->getCode()->willReturn('cross_sell');
        $myCrossSell->getAssociationType()->willReturn($crossSell);
        $myCrossSell->getGroups()->willReturn([]);
        $myCrossSell->getProducts()->willReturn([]);
        $myCrossSell->getProductModels()->willReturn(new ArrayCollection());
        $upSell->getCode()->willReturn('up_sell');
        $myUpSell->getAssociationType()->willReturn($upSell);
        $associatedGroup1->getCode()->willReturn('associated_group1');
        $associatedGroup2->getCode()->willReturn('associated_group2');
        $myUpSell->getGroups()->willReturn([$associatedGroup1, $associatedGroup2]);

        $skuAssocProduct1->getAttributeCode()->willReturn('sku');
        $skuAssocProduct2->getAttributeCode()->willReturn('sku');
        $skuAssocProduct1->__toString()->willReturn('sku_assoc_product1');
        $skuAssocProduct2->__toString()->willReturn('sku_assoc_product2');

        $associatedProduct1->getIdentifier()->willReturn($skuAssocProduct1);
        $associatedProduct2->getIdentifier()->willReturn($skuAssocProduct2);
        $myUpSell->getProducts()->willReturn([$associatedProduct1, $associatedProduct2]);

        $obi = new ProductModel();
        $obi->setCode('obi');
        $wan = new ProductModel();
        $wan->setCode('wan');
        $myUpSell->getProductModels()->willReturn(
            new ArrayCollection([
                $obi,
                $wan
            ])
        );

        $product->isVariant()->willReturn(false);
        $product->getIdentifier()->willReturn($sku);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['group1,group2', 'group_3']);
        $product->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $product->getAssociations()->willReturn([$myCrossSell, $myUpSell]);
        $product->getValues()->willReturn($values);
        $product->getParent()->willReturn(null);
        $filter->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())->willReturn([$sku]);

        $quantifiedAssociationsNormalizer->normalize($product, 'flat', Argument::any())->shouldBeCalled()->willReturn([
            'set-products' => 'sku_assoc_product1,sku_assoc_product2',
            'set-products-quantity' => '14|2',
            'set-product_models' => 'obi,one',
            'set-product_models-quantity' => '0|1',
        ]);

        $normalizer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family' => 'shoes',
                'groups' => 'group1,group2,group_3',
                'categories' => 'nice shoes,converse',
                'parent'     => '',
                'cross_sell-groups' => '',
                'cross_sell-products' => '',
                'cross_sell-product_models' => '',
                'up_sell-groups' => 'associated_group1,associated_group2',
                'up_sell-products' => 'sku_assoc_product1,sku_assoc_product2',
                'up_sell-product_models' => 'obi,wan',
                'set-products' => 'sku_assoc_product1,sku_assoc_product2',
                'set-products-quantity' => '14|2',
                'set-product_models' => 'obi,one',
                'set-product_models-quantity' => '0|1',
                'sku' => 'sku-001',
                'enabled' => 1,
            ]
        );
    }

    function it_normalizes_product_with_a_multiselect_value(
        $filter,
        NormalizerInterface $normalizer,
        ProductInterface $product,
        ValueInterface $sku,
        ValueInterface $colors,
        WriteValueCollection $values,
        FamilyInterface $family,
        QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer
    ) {
        $family->getCode()->willReturn('shoes');

        $sku->getAttributeCode()->willReturn('sku');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $sku->getData()->willReturn('sku-001');

        $colors->getAttributeCode()->willReturn('color');
        $colors->isLocalizable()->willReturn(false);
        $colors->isScopable()->willReturn(false);
        $colors->getData()->willReturn(['red', 'blue']);

        $product->isVariant()->willReturn(false);
        $product->getIdentifier()->willReturn($sku);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn([]);
        $product->getCategoryCodes()->willReturn([]);
        $product->getAssociations()->willReturn([]);
        $product->getValues()->willReturn($values);
        $product->getParent()->willReturn(null);
        $filter
            ->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())
            ->willReturn([$sku, $colors]);

        $normalizer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);
        $normalizer->normalize($colors, 'flat', Argument::any())->willReturn(['colors' => 'red, blue']);
        $quantifiedAssociationsNormalizer->normalize($product, 'flat', Argument::any())->shouldBeCalled()->willReturn([]);

        $this->normalize($product, 'flat', [])->shouldReturn(
            [
                'family'     => 'shoes',
                'groups'     => '',
                'categories' => '',
                'parent'     => '',
                'colors'     => 'red, blue',
                'sku'        => 'sku-001',
                'enabled'    => 1,
            ]
        );
    }

    function it_normalizes_product_with_price(
        $filter,
        ProductInterface $product,
        ValueInterface $price,
        Collection $prices,
        WriteValueCollection $values,
        ProductPriceInterface $productPrice,
        FamilyInterface $family,
        NormalizerInterface $normalizer,
        QuantifiedAssociationsNormalizer $quantifiedAssociationsNormalizer
    ) {
        $family->getCode()->willReturn('shoes');

        $price->getAttributeCode()->willReturn('price');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $price->getData()->willReturn('sku-001');

        $productPrice->getData()->willReturn("356.00");
        $productPrice->getCurrency()->willReturn("EUR");

        $prices->add($productPrice);

        $price->getData()->willReturn($prices);

        $product->isVariant()->willReturn(false);
        $product->getIdentifier()->willReturn($price);
        $product->getFamily()->willReturn($family);
        $product->isEnabled()->willReturn(true);
        $product->getGroupCodes()->willReturn(['group1', 'group2', 'group_3']);
        $product->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $product->getAssociations()->willReturn([]);
        $product->getParent()->willReturn(null);

        $values->add($price);

        $product->getValues()->willReturn($values);
        $filter->filterCollection($values, 'pim.transform.product_value.flat', Argument::cetera())->willReturn(
            [$price]
        );

        $quantifiedAssociationsNormalizer->normalize($product, 'flat', Argument::any())->shouldBeCalled()->willReturn([]);
        $normalizer->normalize($price, 'flat', Argument::any())->willReturn(['price-EUR' => '356.00']);

        $this->normalize($product, 'flat', ['price-EUR' => ''])->shouldReturn(
            [
                'family'     => 'shoes',
                'groups'     => 'group1,group2,group_3',
                'categories' => 'nice shoes,converse',
                'parent'     => '',
                'price-EUR'  => '356.00',
                'enabled'    => 1,
            ]
        );
    }
}
