<?php

namespace spec\Pim\Bundle\ConnectorBundle\Doctrine\Common\Detacher;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;

class StoredProductDetacherSpec extends ObjectBehavior
{
    function let(ObjectDetacherInterface $objectDetacher)
    {
        $this->beConstructedWith($objectDetacher);
    }

    function it_detaches_stored_products(
        $objectDetacher,
        ProductInterface $productA,
        ProductInterface $productB
    ) {
        $this->storeProductToDetach($productA);
        $this->storeProductToDetach($productB);

        $productA->getGroups()->willReturn([]);
        $productA->getAssociations()->willReturn([]);
        $productB->getGroups()->willReturn([]);
        $productB->getAssociations()->willReturn([]);

        $objectDetacher->detach($productA)->shouldBeCalled();
        $objectDetacher->detach($productB)->shouldBeCalled();

        $this->detachStoredProducts([$productA, $productB]);
    }

    function it_detaches_stored_product_with_groups(
        $objectDetacher,
        ProductInterface $product,
        GroupInterface $groupA,
        GroupInterface $groupB,
        Collection $groupCollection,
        \Iterator $groupIterator
    ) {
        $this->storeProductToDetach($product);

        $product->getGroups()->willReturn($groupCollection);
        $groupCollection->getIterator()->willReturn($groupIterator);
        $groupIterator->rewind()->shouldBeCalled();
        $groupIterator->next()->shouldBeCalled();
        $groupIterator->valid()->willReturn(true, true, false);
        $groupIterator->current()->willReturn($groupA, $groupB);

        $product->getAssociations()->willReturn([]);

        $objectDetacher->detach($groupA)->shouldBeCalled();
        $objectDetacher->detach($groupB)->shouldBeCalled();
        $objectDetacher->detach($product)->shouldBeCalled();

        $this->detachStoredProducts([$product]);
    }

    function it_detaches_stored_product_with_associated_products(
        $objectDetacher,
        ProductInterface $product,
        ProductInterface $associatedProductA,
        ProductInterface $associatedProductB,
        ProductInterface $associatedProductC,
        AssociationInterface $associationA,
        AssociationInterface $associationB,
        Collection $assoCollection,
        Collection $productCollectionA,
        Collection $productCollectionB,
        \Iterator $assoIterator,
        \Iterator $productIteratorA,
        \Iterator $productIteratorB
    ) {
        $this->storeProductToDetach($product);

        $product->getGroups()->willReturn([]);

        $product->getAssociations()->willReturn($assoCollection);
        $assoCollection->getIterator()->willReturn($assoIterator);
        $assoIterator->rewind()->shouldBeCalled();
        $assoIterator->next()->shouldBeCalled();
        $assoIterator->valid()->willReturn(true, true, false);
        $assoIterator->current()->willReturn($associationA, $associationB);

        $associationA->getProducts()->willReturn($productCollectionA);
        $productCollectionA->getIterator()->willReturn($productIteratorA);
        $productIteratorA->rewind()->shouldBeCalled();
        $productIteratorA->next()->shouldBeCalled();
        $productIteratorA->valid()->willReturn(true, true, false);
        $productIteratorA->current()->willReturn($associatedProductA, $associatedProductB);

        $associationB->getProducts()->willReturn($productCollectionB);
        $productCollectionB->getIterator()->willReturn($productIteratorB);
        $productIteratorB->rewind()->shouldBeCalled();
        $productIteratorB->next()->shouldBeCalled();
        $productIteratorB->valid()->willReturn(true, false);
        $productIteratorB->current()->willReturn($associatedProductC);

        $associatedProductA->getGroups()->willReturn([]);
        $associatedProductB->getGroups()->willReturn([]);
        $associatedProductC->getGroups()->willReturn([]);

        $objectDetacher->detach($associatedProductA)->shouldBeCalled();
        $objectDetacher->detach($associatedProductB)->shouldBeCalled();
        $objectDetacher->detach($associatedProductC)->shouldBeCalled();
        $objectDetacher->detach($product)->shouldBeCalled();

        $this->detachStoredProducts([$product]);
    }

    function it_detaches_stored_product_with_associated_product_having_group(
        $objectDetacher,
        ProductInterface $product,
        ProductInterface $associatedProduct,
        AssociationInterface $association,
        GroupInterface $group,
        Collection $assoCollection,
        Collection $productCollection,
        Collection $groupCollection,
        \Iterator $assoIterator,
        \Iterator $productIterator,
        \Iterator $groupIterator
    ) {
        $this->storeProductToDetach($product);

        $product->getGroups()->willReturn([]);

        $product->getAssociations()->willReturn($assoCollection);
        $assoCollection->getIterator()->willReturn($assoIterator);
        $assoIterator->rewind()->shouldBeCalled();
        $assoIterator->next()->shouldBeCalled();
        $assoIterator->valid()->willReturn(true, false);
        $assoIterator->current()->willReturn($association);

        $association->getProducts()->willReturn($productCollection);
        $productCollection->getIterator()->willReturn($productIterator);
        $productIterator->rewind()->shouldBeCalled();
        $productIterator->next()->shouldBeCalled();
        $productIterator->valid()->willReturn(true, false);
        $productIterator->current()->willReturn($associatedProduct);

        $associatedProduct->getGroups()->willReturn($groupCollection);
        $groupCollection->getIterator()->willReturn($groupIterator);
        $groupIterator->rewind()->shouldBeCalled();
        $groupIterator->next()->shouldBeCalled();
        $groupIterator->valid()->willReturn(true, false);
        $groupIterator->current()->willReturn($group);

        $objectDetacher->detach($group)->shouldBeCalled();
        $objectDetacher->detach($associatedProduct)->shouldBeCalled();
        $objectDetacher->detach($product)->shouldBeCalled();

        $this->detachStoredProducts([$product]);
    }
}
