<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductModelNormalizer;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_flat_normalization_of_product_model(
        ProductModelInterface $productModel,
        ProductInterface $product
    ) {
        $this->supportsNormalization($productModel, 'flat')->shouldBe(true);
        $this->supportsNormalization($productModel, 'json')->shouldBe(false);
        $this->supportsNormalization($product, 'flat')->shouldBe(false);
    }

    function it_normalizes_a_root_product_model(
        Serializer $serializer,
        ProductModelInterface $productModel,
        ValueInterface $sku,
        WriteValueCollection $values,
        \Iterator $iterator,
        FamilyVariantInterface $familyVariant
    ) {
        $this->setSerializer($serializer);

        $familyVariant->getCode()->willReturn('family_variant_2');
        $productModel->getCode()->willReturn('product_model_1');
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $productModel->getAssociations()->willReturn([]);
        $productModel->getValuesForVariation()->willReturn($values);
        $productModel->getParent()->willReturn(null);

        $values->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($sku);
        $iterator->next()->shouldBeCalled();

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($productModel, 'flat', [])->shouldReturn(
            [
                'family_variant' => 'family_variant_2',
                'code' => 'product_model_1',
                'categories' => 'nice shoes,converse',
                'parent'     => '',
                'sku'        => 'sku-001',
            ]
        );
    }

    function it_normalizes_a_sub_product_model(
        Serializer $serializer,
        ProductModelInterface $productModel,
        ProductModelInterface $parent,
        ValueInterface $sku,
        WriteValueCollection $values,
        \Iterator $iterator,
        FamilyVariantInterface $familyVariant
    ) {
        $this->setSerializer($serializer);

        $familyVariant->getCode()->willReturn('family_variant_2');
        $productModel->getCode()->willReturn('product_model_1');
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $productModel->getAssociations()->willReturn([]);
        $productModel->getValuesForVariation()->willReturn($values);
        $productModel->getParent()->willReturn($parent);
        $parent->getCode()->willReturn('parent_code');

        $values->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($sku);
        $iterator->next()->shouldBeCalled();

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($productModel, 'flat', [])->shouldReturn(
            [
                'family_variant' => 'family_variant_2',
                'code' => 'product_model_1',
                'categories' => 'nice shoes,converse',
                'parent'     => 'parent_code',
                'sku'        => 'sku-001',
            ]
        );
    }

    function it_normalizes_a_product_model_with_associations(
        Serializer $serializer,
        ProductModelInterface $productModel,
        ValueInterface $sku,
        WriteValueCollection $values,
        \Iterator $iterator,
        FamilyVariantInterface $familyVariant,
        ProductModelAssociation $myCrossSell,
        AssociationTypeInterface $crossSell,
        ProductModelAssociation $myUpSell,
        AssociationTypeInterface $upSell,
        GroupInterface $associatedGroup1,
        GroupInterface $associatedGroup2,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ProductModelInterface $associatedProductModel1,
        ProductModelInterface $associatedProductModel2
    ) {
        $this->setSerializer($serializer);

        $familyVariant->getCode()->willReturn('family_variant_2');
        $productModel->getCode()->willReturn('product_model_1');
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $productModel->getAssociations()->willReturn([$myCrossSell, $myUpSell]);
        $productModel->getValuesForVariation()->willReturn($values);

        $values->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($sku);
        $iterator->next()->shouldBeCalled();

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $crossSell->getCode()->willReturn('cross_sell');
        $myCrossSell->getAssociationType()->willReturn($crossSell);
        $myCrossSell->getGroups()->willReturn([]);
        $myCrossSell->getProducts()->willReturn([]);
        $myCrossSell->getProductModels()->willReturn(new ArrayCollection());

        $upSell->getCode()->willReturn('up_sell');
        $myUpSell->getAssociationType()->willReturn($upSell);
        $myUpSell->getGroups()->willReturn([$associatedGroup1, $associatedGroup2]);
        $myUpSell->getProducts()->willReturn([$associatedProduct1, $associatedProduct2]);
        $myUpSell->getProductModels()->willReturn(new ArrayCollection());

        $associatedGroup1->getCode()->willReturn('associated_group1');
        $associatedGroup2->getCode()->willReturn('associated_group2');

        $associatedProduct1->getIdentifier()->willReturn('sku_assoc_product1');
        $associatedProduct2->getIdentifier()->willReturn('sku_assoc_product2');

        $associatedProductModel1->getCode()->willReturn('obi');
        $associatedProductModel2->getCode()->willReturn('wan');

        $productModel->getParent()->willReturn(null);

        $this->normalize($productModel, 'flat', [])->shouldReturn(
            [
                'family_variant' => 'family_variant_2',
                'code' => 'product_model_1',
                'categories' => 'nice shoes,converse',
                'parent'     => '',
                'cross_sell-groups' => '',
                'cross_sell-products' => '',
                'cross_sell-product_models' => '',
                'up_sell-groups' => 'associated_group1,associated_group2',
                'up_sell-products' => 'sku_assoc_product1,sku_assoc_product2',
                'up_sell-product_models' => '',
                'sku' => 'sku-001',
            ]
        );
    }
}
