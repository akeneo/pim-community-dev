<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ParentsAssociationsNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ParentsAssociationsNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ParentsAssociationsNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
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
        $productModelParent->getAllAssociations()->willReturn($parentAssociations);
        $productModelParent->getParent()->willReturn(null);
        $product->getAllAssociations()->willReturn([$association1, $association2]);

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

    function it_normalizes_a_product_associations_with_parent_associations_in_standard_format(
        ProductInterface $product,
        AssociationInterface $subAssociation1,
        AssociationInterface $subAssociation2,
        AssociationInterface $rootAssociation1,
        AssociationTypeInterface $associationType1,
        AssociationTypeInterface $associationType2,
        GroupInterface $subGroup,
        GroupInterface $rootGroup,
        ProductInterface $productAssociated,
        ProductInterface $productAssociatedToRootModel,
        ProductModelInterface $productModelAssociated,
        ProductModelInterface $subProductModel
    ) {
        $subGroup->getCode()->willReturn('group_sub');
        $rootGroup->getCode()->willReturn('group_root');

        $productAssociated->getReference()->willReturn('product_code');
        $productAssociatedToRootModel->getReference()->willReturn('product_associated_to_root');
        $productModelAssociated->getCode()->willReturn('product_model_code');

        $product->getParent()->willReturn($subProductModel);

        $subProductModel->getCode()->willReturn('sub_product_model_code');
        $subProductModel->getAllAssociations()->willReturn([
            $subAssociation1,
            $subAssociation2,
            $rootAssociation1,
        ]);

        $associationType1->getCode()->willReturn('XSELL');
        $associationType2->getCode()->willReturn('PACK');

        $subAssociation1->getAssociationType()->willReturn($associationType1);
        $subAssociation1->getGroups()->willReturn(new ArrayCollection([$subGroup->getWrappedObject()]));
        $subAssociation1->getProducts()->willReturn(new ArrayCollection());
        $subAssociation1->getProductModels()->willReturn(new ArrayCollection());

        $rootAssociation1->getAssociationType()->willReturn($associationType1);
        $rootAssociation1->getGroups()->willReturn(new ArrayCollection([$rootGroup->getWrappedObject()]));
        $rootAssociation1->getProducts()->willReturn(new ArrayCollection([
            $productAssociated->getWrappedObject(),
            $productAssociatedToRootModel->getWrappedObject(),
        ]));
        $rootAssociation1->getProductModels()->willReturn(new ArrayCollection());

        $subAssociation2->getAssociationType()->willReturn($associationType2);
        $subAssociation2->getGroups()->willReturn(new ArrayCollection());
        $subAssociation2->getProducts()->willReturn(new ArrayCollection([$productAssociated->getWrappedObject()]));
        $subAssociation2->getProductModels()->willReturn(new ArrayCollection([$productModelAssociated->getWrappedObject()]));

        $parentAssociationsNormalized = [
            'PACK' => [
                'groups' => [],
                'products' => ['product_code'],
                'product_models' => ['product_model_code'],
            ],
            'XSELL' => [
                'groups' => ['group_sub', 'group_root'],
                'products' => ['product_code', 'product_associated_to_root'],
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
        $productModelParent->getAllAssociations()->willReturn([]);
        $productModelParent->getParent()->willReturn(null);
        $this->normalize($product, 'standard')->shouldReturn([]);
    }
}
