<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

class ParentsAssociationsNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\Product\ParentsAssociationsNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_format_and_product_only(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'standard')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_a_product_associations_in_standard_format_only(
        ProductInterface $product,
        AssociationInterface $association1,
        AssociationInterface $association2,
        AssociationTypeInterface $associationType1,
        AssociationTypeInterface $associationType2,
        GroupInterface $group1,
        ProductInterface $productAssociated,
        ProductModelInterface $productModelAssociated,
        ProductModelInterface $productModelParent
    ) {
        $group1->getCode()->willReturn('group_code');
        $associationType1->getCode()->willReturn('XSELL');
        $association1->getAssociationType()->willReturn($associationType1);
        $association1->getGroups()->willReturn(new ArrayCollection([$group1->getWrappedObject()]));
        $association1->getProducts()->willReturn(new ArrayCollection());
        $association1->getProductModels()->willReturn(new ArrayCollection());

        $productAssociated->getReference()->willReturn('product_code');
        $associationType2->getCode()->willReturn('PACK');
        $association2->getAssociationType()->willReturn($associationType2);
        $association2->getGroups()->willReturn(new ArrayCollection());
        $association2->getProducts()->willReturn(new ArrayCollection([$productAssociated->getWrappedObject()]));

        $productModelAssociated->getCode()->willReturn('product_model_code');
        $association2->getProductModels()->willReturn(new ArrayCollection([$productModelAssociated->getWrappedObject()]));

        $product->getParent()->willReturn($productModelParent);

        $parentAssociations = [
            $association1,
            $association2,
        ];

        $productModelParent->getCode()->willReturn('product_model_parent_code');
        $productModelParent->getAssociations()->willReturn($parentAssociations);
        $product->getAssociations()->willReturn([$association1, $association2]);

        $parentAssociationsNormalized = [
            'PACK' => [
                'groups' => [],
                'products' => ['product_code'],
                'product_models' => ['product_model_code'],
            ],
            'XSELL' => [
                'groups' => ['group_code'],
                'products' => [],
                'product_models' => [],
            ]
        ];

        $this->normalize($product, 'standard')->shouldReturn($parentAssociationsNormalized);
    }

    function it_normalizes_a_product_with_no_parent_associations(
        ProductInterface $product,
        ProductModelInterface $productModelParent
    ) {
        $product->getParent()->willReturn($productModelParent);
        $productModelParent->getAssociations()->willReturn([]);
        $this->normalize($product, 'standard')->shouldReturn([]);
    }
}
