<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductModelNormalizer;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelAssociation;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;

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

    function it_is_serializer_aware()
    {
        $this->shouldImplement(SerializerAwareInterface::class);
    }

    function it_supports_flat_normalization_of_product_model(
        ProductModelInterface $productModel,
        ProductInterface $product
    ) {
        $this->supportsNormalization($productModel, 'flat')->shouldBe(true);
        $this->supportsNormalization($productModel, 'json')->shouldBe(false);
        $this->supportsNormalization($product, 'flat')->shouldBe(false);
    }

    function it_normalizes_a_product_model(
        Serializer $serializer,
        ProductModelInterface $productModel,
        ValueInterface $sku,
        ValueCollectionInterface $values,
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
                'sku' => 'sku-001',
            ]
        );
    }

    function it_normalizes_a_product_model_with_associations(
        Serializer $serializer,
        ProductModelInterface $productModel,
        ValueInterface $sku,
        ValueCollectionInterface $values,
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
        ProductModelInterface $associatedProductModel2,
        ArrayCollection $associatedProductModelsCollection,
        \ArrayIterator $associatedProductsModelsIterator
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

        $this->normalize($productModel, 'flat', [])->shouldReturn(
            [
                'family_variant' => 'family_variant_2',
                'code' => 'product_model_1',
                'categories' => 'nice shoes,converse',
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
