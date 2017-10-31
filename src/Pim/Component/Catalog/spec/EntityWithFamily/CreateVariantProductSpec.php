<?php

namespace spec\Pim\Component\Catalog;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProduct;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Prophecy\Argument;

class CreateVariantProductSpec extends ObjectBehavior
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

        $this->beConstructedWith(VariantProduct::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateVariantProduct::class);
    }

    function it_throws_an_exception_when_the_product_has_not_the_same_family_that_its_parent(
        ProductInterface $product,
        FamilyInterface $productFamily,
        ProductModelInterface $parent
    ) {
        $product->getFamily()->willReturn($productFamily);

        $this->shouldThrow(\Exception::class)->during('from', [$product, $parent]);
    }

    public function it_turns_a_product_into_a_variant_product(
        ProductModelInterface $parent,
        ValueCollectionInterface $parentValues,
        ProductInterface $product,
        Collection $groups,
        Collection $associations,
        Collection $completenesses,
        Collection $categories,
        FamilyInterface $family,
        \Datetime $createdAt,
        \Datetime $updatedAt,
        ValueCollectionInterface $values,
        ValueInterface $valueSku,
        AttributeInterface $sku
    ) {
        $product->getFamily()->willReturn($family);
        $parent->getFamily()->willReturn($family);
        $parent->getValues()->willReturn($parentValues);

        $categories->toArray()->willReturn([]);
        $values->toArray()->willReturn([]);
        $values->first()->willReturn($valueSku);

        $valueSku->getData()->willReturn('foo');
        $valueSku->getAttribute()->willReturn($sku);
        $valueSku->getScope()->willReturn(null);
        $valueSku->getLocale()->willReturn(null);

        $values->filter(Argument::any())->willReturn($values);

        $product->getId()->willReturn(42);
        $product->getGroups()->willReturn($groups);
        $product->getAssociations()->willReturn($associations);
        $product->isEnabled()->willReturn(true);
        $product->getCompletenesses()->willReturn($completenesses);
        $product->getFamily()->willReturn($family);
        $product->getCategories()->willReturn($categories);
        $product->getValues()->willReturn($values);
        $product->getCreated()->willReturn($createdAt);
        $product->getUpdated()->willReturn($updatedAt);

        $result = $this->from($product, $parent);
        $result->shouldReturnAnInstanceOf(VariantProductInterface::class);
        $result->getId()->shouldReturn(42);
        $result->getIdentifier()->shouldReturn('foo');
        $result->isEnabled()->shouldReturn(true);
        $result->getFamily()->shouldReturn($family);
        $result->getCreated()->shouldReturn($createdAt);
        $result->getUpdated()->shouldReturn($updatedAt);
        $result->getGroups()->shouldReturnAnInstanceOf(Collection::class);
        $result->getAssociations()->shouldReturnAnInstanceOf(Collection::class);
        $result->getCompletenesses()->shouldReturnAnInstanceOf(Collection::class);
        $result->getCategories()->shouldReturnAnInstanceOf(Collection::class);
        $result->getValues()->shouldReturnAnInstanceOf(ValueCollectionInterface::class);
    }
}
