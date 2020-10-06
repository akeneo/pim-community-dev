<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenRemovedFromVariantProduct;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParent;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Tool\Component\Classification\Model\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RemoveParentSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveParent::class);
    }

    function it_throws_an_exception_if_the_product_is_not_variant(
        ProductInterface $product
    ) {
        $product->isVariant()->willReturn(false);
        $this->shouldThrow(\InvalidArgumentException::class)->during('from', [$product]);
    }

    function it_does_nothing_for_a_new_product(
        EventDispatcherInterface $eventDispatcher,
        ProductInterface $product
    ) {
        $product->isVariant()->willReturn(true);
        $product->getId()->willReturn(null);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->from($product);
    }

    function it_keeps_the_ancestor_values_categories_and_associations(
        EventDispatcherInterface $eventDispatcher,
        ProductInterface $product,
        ProductModelInterface $parentProductModel,
        Collection $productCategories,
        AssociationType $xsell,
        ProductAssociationInterface $association,
        Collection $associatedProducts,
        Collection $associatedProductModels,
        Collection $associatedGroups,
        ProductModelAssociationInterface $parentAssociation,
        ProductInterface $someProduct,
        ProductInterface $otherProduct,
        ProductModelInterface $someProductModel,
        ProductModelInterface $otherProductModel,
        GroupInterface $someGroup,
        GroupInterface $otherGroup
    ) {
        $allValues = new WriteValueCollection(
            [
                ScalarValue::value('sku', 'tshirt'),
                ScalarValue::localizableValue('name', 'My great red t-shirt', 'en_US'),
                OptionValue::value('color', 'red'),
            ]
        );
        $product->isVariant()->willReturn(true);
        $product->getId()->willReturn(42);
        $product->getValues()->willReturn($allValues);
        $parentCategory = new Category();
        $childCategory = new Category();
        $product->getCategories()->willReturn(new ArrayCollection([$parentCategory, $childCategory]));
        $product->getCategoriesForVariation()->willReturn($productCategories);

        $xsell->getCode()->willReturn('XSELL');
        $parentAssociation->getAssociationType()->willReturn($xsell);
        $parentAssociation->getProducts()->willReturn(new ArrayCollection([$someProduct->getWrappedObject()]));
        $parentAssociation->getProductModels()->willReturn(new ArrayCollection([$someProductModel->getWrappedObject()]));
        $parentAssociation->getGroups()->willReturn(new ArrayCollection([$someGroup->getWrappedObject()]));

        $association->getAssociationType()->willReturn($xsell);
        $associatedProducts->getIterator()->willReturn(new \ArrayIterator([$otherProduct]));
        $association->getProducts()->willReturn($associatedProducts);
        $associatedProductModels->getIterator()->willReturn(new \ArrayIterator([$otherProductModel]));
        $association->getProductModels()->willReturn($associatedProductModels);
        $associatedGroups->getIterator()->willReturn(new \ArrayIterator([$otherGroup]));
        $association->getGroups()->willReturn($associatedGroups);
        $product->getAssociationForTypeCode('XSELL')->willReturn($association);

        $product->getAllAssociations()->willReturn(
            new ArrayCollection([$parentAssociation->getWrappedObject(), $association->getWrappedObject()])
        );

        $parentProductModel->getCode()->willReturn('tshirt_model');
        $product->getParent()->willReturn($parentProductModel);

        // values
        $product->setValues($allValues)->shouldBeCalled();

        // categories
        $productCategories->contains($parentCategory)->shouldBeCalled()->willReturn(false);
        $productCategories->add($parentCategory)->shouldBeCalled();
        $productCategories->contains($childCategory)->shouldBeCalled()->willReturn(true);
        $productCategories->add($childCategory)->shouldNotBeCalled();

        // associations
        $associatedProducts->contains($someProduct)->shouldBeCalled()->willReturn(false);
        $association->addProduct($someProduct)->shouldBeCalled();
        $associatedProductModels->contains($someProductModel)->shouldBeCalled()->willReturn(false);
        $association->addProductModel($someProductModel)->shouldBeCalled();
        $associatedGroups->contains($someGroup)->shouldBeCalled()->willReturn(false);
        $association->addGroup($someGroup)->shouldBeCalled();

        $associatedProducts->contains($otherProduct)->shouldBeCalled()->willReturn(true);
        $association->addProduct($otherProduct)->shouldNotBeCalled();
        $associatedProductModels->contains($otherProductModel)->shouldBeCalled()->willReturn(true);
        $association->addProductModel($otherProductModel)->shouldNotBeCalled();
        $associatedGroups->contains($otherGroup)->shouldBeCalled()->willReturn(true);
        $association->addGroup($otherGroup)->shouldNotBeCalled();

        $parentProductModel->removeProduct($product)->shouldBeCalled();
        $product->setParent(null)->shouldBeCalled();
        $eventDispatcher->dispatch(
            new ParentHasBeenRemovedFromVariantProduct($product->getWrappedObject(), 'tshirt_model')
        )->shouldBeCalled();

        $this->from($product);
    }
}
