<?php

namespace spec\Pim\Component\Catalog\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\UniqueAxesCombinationSet;

class UniqueAxesCombinationSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueAxesCombinationSet::class);
    }

    function it_adds_new_axis_combinations(
        GroupInterface $variantGroup1,
        GroupInterface $variantGroup2,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $variantGroup1->getCode()->willReturn('variant_group_1');
        $variantGroup2->getCode()->willReturn('variant_group_2');

        $product1->getIdentifier()->willReturn('product_1');
        $product1->getVariantGroup()->willReturn($variantGroup1);

        $product2->getIdentifier()->willReturn('product_2');
        $product2->getVariantGroup()->willReturn($variantGroup1);

        $product3->getIdentifier()->willReturn('product_3');
        $product3->getVariantGroup()->willReturn($variantGroup2);

        $this->addCombination($product1, 'size-xl,color-red')->shouldReturn(true);
        $this->addCombination($product2, 'size-xl,color-red')->shouldReturn(false);
        $this->addCombination($product3, 'size-xl,color-red')->shouldReturn(true);
    }
}
