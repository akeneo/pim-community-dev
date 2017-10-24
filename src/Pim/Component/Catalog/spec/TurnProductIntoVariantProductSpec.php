<?php

namespace spec\Pim\Component\Catalog;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\TurnProductIntoVariantProduct;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TurnProductIntoVariantProductSpec extends ObjectBehavior
{
    function let(
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        AttributeInterface $description,
        AttributeInterface $color
    ) {
        $familyVariant->getFamily()->willReturn($family);
        $familyVariant->getCommonAttributes()->willReturn([$description]);
        $familyVariant->getAxes()->willReturn([$color]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TurnProductIntoVariantProduct::class);
    }

    function it_turns_a_product_into_a_variant_product(ProductInterface $product, ProductModelInterface $parent)
    {
        $this->turnInto($product, $parent)->shouldReturnAnInstanceOf(VariantProductInterface::class);
    }

    function it_throws_an_exception_when_the_product_has_not_the_same_family_that_its_parent(
        ProductInterface $product,
        FamilyInterface $productFamily,
        ProductModelInterface $parent
    ) {
        $product->getFamily()->willReturn($productFamily);

        $this->shouldThrow(\Exception::class)->during('turnInto', [$product, $parent]);
    }

    function it_filters_product_values_to_remove_ancestry_values(
        ProductInterface $product,
        ProductModelInterface $parent,
        ValueCollectionInterface $productValues,
        ValueCollectionInterface $parentValues,
        ValueCollectionInterface $productFilteredValues
     ) {
        $product->getValues()->willReturn($productValues);
        $parent->getValues()->willReturn($parentValues);

        $productValues->filter(Argument::type(\Closure::class))->willReturn($productFilteredValues);
        $product->setValues($productFilteredValues)->shouldBeCalled();

        $this->filter($product, $parent)->shouldBeAnInstanceOf($product);
    }
}
