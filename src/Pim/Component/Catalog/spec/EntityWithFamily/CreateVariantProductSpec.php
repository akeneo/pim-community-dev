<?php

namespace spec\Pim\Component\Catalog\EntityWithFamily;

use Doctrine\Common\Collections\ArrayCollection;
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
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
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

    function it is initializable()
    {
        $this->shouldHaveType(CreateVariantProduct::class);
    }

    function it throws an exception when the product has not the same family that its parent(
        ProductInterface $product,
        FamilyInterface $productFamily,
        ProductModelInterface $parent
    ) {
        $product->getFamily()->willReturn($productFamily);

        $this->shouldThrow(\InvalidArgumentException::class)->during('from', [$product, $parent]);
    }

    public function it creates a variant product from a product(
        ProductModelInterface $parent,
        ValueCollectionInterface $parentValues,
        ProductInterface $product,
        Collection $groups,
        Collection $associations,
        Collection $completenesses,
        Collection $categories,
        Collection $productModelCategories,
        ValueCollectionInterface $productModelValues,
        FamilyInterface $family,
        \Datetime $createdAt,
        \Datetime $updatedAt,
        ValueCollectionInterface $values,
        ValueInterface $valueSku,
        AttributeInterface $sku,
        FamilyVariantInterface $familyVariant,
        \Iterator $iterator,
        ArrayCollection $uniqueValues,
        ArrayCollection $attributes,
        VariantAttributeSetInterface $variantAttributeSet
    ) {
        $parent->getFamily()->willReturn($family);
        $parent->getValues()->willReturn($parentValues);
        $parent->getValuesForVariation()->willReturn($productModelValues);
        $productModelValues->getIterator()->willReturn($iterator);
        $parent->getFamilyVariant()->willreturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(2);
        $familyVariant->getVariantAttributeSet(2)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($attributes);
        $parent->getParent()->willreturn(null);
        $parent->getCategories()->willreturn($productModelCategories);
        $productModelCategories->getIterator()->willReturn($iterator);

        $categories->toArray()->willReturn([]);
        $values->toArray()->willReturn([]);
        $values->first()->willReturn($valueSku);

        $valueSku->getData()->willReturn('foo');
        $valueSku->getAttribute()->willReturn($sku);
        $valueSku->getScope()->willReturn(null);
        $valueSku->getLocale()->willReturn(null);

        $values->filter(Argument::any())->willReturn($values);

        $product->getId()->willReturn(42);
        $product->getFamily()->willReturn($family);
        $product->getGroups()->willReturn($groups);
        $product->getAssociations()->willReturn($associations);
        $product->isEnabled()->willReturn(true);
        $product->getCompletenesses()->willReturn($completenesses);
        $product->getFamily()->willReturn($family);
        $product->getCategories()->willReturn($categories);
        $product->getValues()->willReturn($values);
        $product->getCreated()->willReturn($createdAt);
        $product->getUpdated()->willReturn($updatedAt);
        $product->getUniqueData()->willReturn($uniqueValues);

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
        $result->getFamilyVariant()->shouldReturnAnInstanceOf($familyVariant);
        $result->getUniqueData()->shouldReturnAnInstanceOf($uniqueValues);
    }
}
