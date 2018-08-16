<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\AssociatedProduct\GetAssociatedProductCodesByProduct;

class AssociationsNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\Product\AssociationsNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
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
                ]
            ]
        );
    }

    function it_normalizes_a_product_with_no_associations(ProductInterface $product)
    {
        $product->getAllAssociations()->willReturn([]);
        $this->normalize($product, 'standard')->shouldReturn([]);
    }

    function it_normalizes_a_product_associations_with_query_to_find_associated_products_codes(
        GetAssociatedProductCodesByProduct $getAssociatedProductCodesByProduct,
        ProductInterface $product,
        AssociationInterface $association1,
        AssociationInterface $association2,
        AssociationTypeInterface $associationType1,
        AssociationTypeInterface $associationType2,
        GroupInterface $group1,
        ProductInterface $productAssociated,
        \ArrayIterator $association1Iterator,
        \ArrayIterator $association2Iterator
    ) {
        $this->beConstructedWith($getAssociatedProductCodesByProduct);
        $product->getId()->willReturn(1);

        $group1->getCode()->willReturn('group_code');
        $associationType1->getCode()->willReturn('XSELL');
        $associationType1->getId()->willReturn(5);
        $association1->getAssociationType()->willReturn($associationType1);
        $association1->getGroups()->willReturn($association1Iterator);
        $association1->getProducts()->willReturn(new ArrayCollection());
        $getAssociatedProductCodesByProduct->getCodes(1, 5)->willReturn([]);

        $association1Iterator->rewind()->willReturn($group1);
        $valueCount = 1;
        $association1Iterator->valid()->will(
            function () use (&$valueCount) {
                return $valueCount-- > 0;
            }
        );
        $association1Iterator->current()->willReturn($group1);
        $association1Iterator->next()->willReturn(null);
        $association1Iterator->count()->willReturn(1);

        $productAssociated->getReference()->willReturn('product_code');
        $associationType2->getCode()->willReturn('PACK');
        $associationType2->getId()->willReturn(7);
        $association2->getAssociationType()->willReturn($associationType2);
        $association2->getGroups()->willReturn(new ArrayCollection());
        $association2->getProducts()->willReturn($association2Iterator);

        $getAssociatedProductCodesByProduct->getCodes(1, 7)->willReturn(['product_code']);

        $association2Iterator->rewind()->willReturn($productAssociated);
        $valueCount2 = 1;
        $association2Iterator->valid()->will(
            function () use (&$valueCount2) {
                return $valueCount2-- > 0;
            }
        );
        $association2Iterator->current()->willReturn($productAssociated);
        $association2Iterator->next()->willReturn(null);
        $association2Iterator->count()->willReturn(1);

        $product->getAssociations()->willReturn([$association1, $association2]);

        $this->normalize($product, 'standard')->shouldReturn(
            [
                'PACK' => [
                    'groups' => [],
                    'products' => ['product_code']
                ],
                'XSELL' => [
                    'groups' => ['group_code'],
                    'products' => []
                ]
            ]
        );
    }
}
