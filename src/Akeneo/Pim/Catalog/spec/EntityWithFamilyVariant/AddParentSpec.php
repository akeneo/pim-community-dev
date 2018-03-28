<?php

namespace spec\Pim\Component\Catalog\EntityWithFamilyVariant;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\EntityWithFamilyVariant\AddParent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AddParentSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($productModelRepository, $eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddParent::class);
    }

    function it_adds_a_parent_to_a_product_only_when_we_update_product(
        $productModelRepository,
        $eventDispatcher,
        ProductInterface $product,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        ValueCollectionInterface $values,
        ValueCollectionInterface $filteredValues,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributes
    ) {
        $product->getId()->willReturn(40);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $product->getValues()->willReturn($values);
        $values->filter(Argument::any())->willReturn($filteredValues);
        $familyVariant->getVariantAttributeSet(2)->willReturn($attributeSet);
        $familyVariant->getNumberOfLevel()->willReturn(2);
        $attributeSet->getAttributes()->willReturn($attributes);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModelRepository->findOneByIdentifier('parent')->willReturn()->willReturn($productModel);

        $product->setParent($productModel)->shouldBeCalled();
        $product->setFamilyVariant($familyVariant)->shouldBeCalled();
        $product->setValues($filteredValues)->shouldBeCalled();

        $eventDispatcher->dispatch(ParentHasBeenAddedToProduct::EVENT_NAME, Argument::type(ParentHasBeenAddedToProduct::class))
            ->shouldBeCalled();

        $this->to($product, 'parent')->shouldReturn($product);
    }

    function it_does_not_add_any_parent_to_a_product_when_we_create_a_product(
        $productModelRepository,
        $eventDispatcher,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(null);

        $productModelRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $product->setParent(Argument::cetera())->shouldNotBeCalled();
        $product->setValues(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(ParentHasBeenAddedToProduct::EVENT_NAME, Argument::type(ParentHasBeenAddedToProduct::class))
            ->shouldNotBeCalled();

        $this->to($product, '')->shouldReturn($product);
    }


    function it_does_not_add_any_parent_to_a_product_when_the_parent_code_is_invalid(
        $productModelRepository,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(40);

        $productModelRepository->findOneByIdentifier('invalid_parent_code')->willReturn()->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('to', [$product, 'invalid_parent_code']);
    }
}
