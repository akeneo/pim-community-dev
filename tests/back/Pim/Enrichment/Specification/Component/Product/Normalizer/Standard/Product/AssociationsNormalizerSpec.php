<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductUuidsByProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\AssociationsNormalizer;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociationsNormalizerSpec extends ObjectBehavior
{
    function let(GetAssociatedProductUuidsByProduct $getAssociatedProductCodesByProduct)
    {
        $this->beConstructedWith($getAssociatedProductCodesByProduct);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationsNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_format_and_product_only(
        ProductInterface $product
    ) {
        $this->supportsNormalization($product, 'standard')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_a_product_associations_in_standard_format_only(
        GetAssociatedProductUuidsByProduct $getAssociatedProductCodesByProduct,
        ProductInterface $product,
        AssociationInterface $association1,
        AssociationInterface $association2,
        AssociationTypeInterface $associationType1,
        AssociationTypeInterface $associationType2,
        GroupInterface $group1,
        ProductInterface $productAssociated,
        ProductModelInterface $productModelAssociated
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

        $uuid = Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c');
        $product->getUuid()->willReturn($uuid);
        $product->getParent()->willReturn(null);
        $product->getAssociations()->willReturn([$association1, $association2]);

        $getAssociatedProductCodesByProduct->getCodes($uuid, $association1)->willReturn([]);
        $getAssociatedProductCodesByProduct->getCodes($uuid, $association2)->willReturn(['product_code']);

        $this->normalize($product, 'standard')->shouldReturn(
            [
                'PACK' => [
                    'groups' => [],
                    'products' => ['product_code'],
                    'product_models' => ['product_model_code'],
                ],
                'XSELL' => [
                    'groups' => ['group_code'],
                    'products' => [],
                    'product_models' => [],
                ],
            ]
        );
    }

    function it_normalizes_a_product_with_no_associations(ProductInterface $product)
    {
        $product->getParent()->willReturn(null);
        $product->getAssociations()->willReturn([]);
        $this->normalize($product, 'standard')->shouldReturn([]);
    }

    function it_normalizes_a_product_associations_with_query_to_find_associated_products_codes(
        GetAssociatedProductUuidsByProduct $getAssociatedProductCodesByProduct,
        ProductInterface $product,
        ProductModelInterface $productModel,
        AssociationInterface $association1,
        AssociationInterface $association2,
        AssociationTypeInterface $associationType1,
        AssociationTypeInterface $associationType2,
        GroupInterface $group1,
        ProductInterface $productAssociated
    ) {
        $group1->getCode()->willReturn('group_code');
        $associationType1->getCode()->willReturn('XSELL');
        $association1->getAssociationType()->willReturn($associationType1);
        $association1->getGroups()->willReturn([$group1]);
        $association1->getProducts()->willReturn(new ArrayCollection());
        $association1->getProductModels()->willReturn(new ArrayCollection());
        $uuid = Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c');
        $product->getUuid()->willReturn($uuid);
        $getAssociatedProductCodesByProduct->getCodes($uuid, $association1)->willReturn([]);

        $productAssociated->getReference()->willReturn('product_code');
        $productModel->getCode()->willReturn('product_model_code');
        $associationType2->getCode()->willReturn('PACK');
        $associationType2->getId()->willReturn(7);
        $association2->getAssociationType()->willReturn($associationType2);
        $association2->getGroups()->willReturn(new ArrayCollection());
        $association2->getProducts()->willReturn([$productAssociated->getWrappedObject()]);
        $association2->getProductModels()->willReturn(new ArrayCollection([$productModel->getWrappedObject()]));

        $getAssociatedProductCodesByProduct->getCodes($uuid, $association2)->willReturn(['product_code']);

        $product->getParent()->willReturn(null);
        $product->getAssociations()->willReturn([$association1, $association2]);
        $product->getAllAssociations()->willReturn([$association1, $association2]);

        $this->normalize($product, 'standard')->shouldReturn(
            [
                'PACK' => [
                    'groups' => [],
                    'products' => ['product_code'],
                    'product_models' => ['product_model_code'],
                ],
                'XSELL' => [
                    'groups' => ['group_code'],
                    'products' => [],
                    'product_models' => [],
                ],
            ]
        );
    }
}
