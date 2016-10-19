<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;

class ProductAssociationsNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Structured\ProductAssociationsNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_products_in_json_and_xml(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'json')->shouldReturn(true);
        $this->supportsNormalization($product, 'xml')->shouldReturn(true);
        $this->supportsNormalization($product, 'csv')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_other_entities(AttributeInterface $attribute)
    {
        $this->supportsNormalization($attribute, 'json')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'xml')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'json')->shouldReturn(false);
    }

    function it_normalizes_the_associations_of_the_product(
        ProductInterface $product,
        AssociationInterface $association1,
        AssociationInterface $association2,
        AssociationTypeInterface $type1,
        AssociationTypeInterface $type2,
        ProductInterface $productAssociated1,
        ProductInterface $productAssociated2,
        ProductInterface $productAssociated3,
        GroupInterface $groupAssociated1,
        GroupInterface $groupAssociated2
    ) {
        $type1->getCode()->willReturn('wahou the type');
        $type2->getCode()->willReturn('such a type');

        $groupAssociated1->getCode()->willReturn('group 1');
        $groupAssociated2->getCode()->willReturn('group 2');

        $productAssociated1->getReference()->willReturn('product 1');
        $productAssociated2->getReference()->willReturn('product 2');
        $productAssociated3->getReference()->willReturn('product 3');

        $association1->getAssociationType()->willReturn($type1);
        $association2->getAssociationType()->willReturn($type2);

        $association1->getGroups()->willReturn([$groupAssociated1]);
        $association2->getGroups()->willReturn([$groupAssociated1, $groupAssociated2]);

        $association1->getProducts()->willReturn([]);
        $association2->getProducts()->willReturn([$productAssociated1, $productAssociated2, $productAssociated3]);

        $product->getAssociations()->willReturn([$association1, $association2]);

        $this->normalize($product, 'json')->shouldReturn([
            'such a type' => [
                'groups' => ['group 1', 'group 2'],
                'products' => ['product 1', 'product 2', 'product 3'],
            ],
            'wahou the type' => [
                'groups' => ['group 1'],
                'products' => [],
            ],
        ]);
    }
}
