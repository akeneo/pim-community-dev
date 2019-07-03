<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Doctrine\Common\Collections\ArrayCollection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
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
        WriteValueCollection $values,
        WriteValueCollection $filteredValues,
        VariantAttributeSetInterface $attributeSet
    ) {
        $product->getId()->willReturn(40);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $product->getValues()->willReturn($values);
        $values->filter(Argument::any())->willReturn($filteredValues);
        $familyVariant->getVariantAttributeSet(2)->willReturn($attributeSet);
        $familyVariant->getNumberOfLevel()->willReturn(2);
        $attributeSet->getAttributes()->willReturn(new ArrayCollection());
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
