<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

class TwoWayAssociationUpdaterSpec extends ObjectBehavior
{
    function let(
        MissingAssociationAdder $missingAssociationAdder,
        ManagerRegistry $registry,
        EntityManager $entityManager
    ) {
        $registry->getManager()->willReturn($entityManager);

        $this->beConstructedWith($registry, $missingAssociationAdder);
    }

    function it_creates_missing_association_on_reverse_association_when_missing(
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $associatedProduct,
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductInterface $associationOwner
    ) {
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);

        $associatedProduct->getAssociationForType($associationType)->willReturn(null, $inversedAssociation);
        $missingAssociationAdder->addMissingAssociations($associatedProduct)->shouldBeCalled();
        $inversedAssociation->getProducts()->willReturn(new ArrayCollection([]));

        $associatedProduct->removeAssociation($inversedAssociation)->shouldBeCalled();
        $inversedAssociation->addProduct($associationOwner)->shouldBeCalled();
        $associatedProduct->addAssociation($inversedAssociation)->shouldBeCalled();

        $this->createInversedAssociation($association, $associatedProduct);
    }

    function it_adds_product_model_on_inversed_association_when_owner_is_product_model(
        ProductInterface $product,
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductModelInterface $associationOwner
    ) {
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);

        $product->getAssociationForType($associationType)->willReturn($inversedAssociation);
        $inversedAssociation->getProductModels()->willReturn(new ArrayCollection([]));

        $inversedAssociation->addProductModel($associationOwner)->shouldBeCalled();

        $this->createInversedAssociation($association, $product);
    }

    function it_replaces_product_from_association_when_association_already_contain_another_instance_of_the_product(
        ProductInterface $associatedProduct,
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductInterface $associationOwner,
        ProductInterface $associationOwnerClone
    ) {
        $associationOwner->getIdentifier()->willreturn('58');
        $associationOwnerClone->getIdentifier()->willreturn('58');

        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);

        $associatedProduct->getAssociationForType($associationType)->willReturn($inversedAssociation);
        $inversedAssociation->getProducts()->willReturn([$associationOwnerClone]);

        $inversedAssociation->removeProduct($associationOwnerClone)->shouldBeCalled();

        $associatedProduct->removeAssociation($inversedAssociation)->shouldBeCalled();
        $inversedAssociation->addProduct($associationOwner)->shouldBeCalled();
        $associatedProduct->addAssociation($inversedAssociation)->shouldBeCalled();

        $this->createInversedAssociation($association, $associatedProduct);
    }

    function it_replaces_product_model_from_association_when_association_already_contain_another_instance_of_the_product_model(
        ProductInterface $product,
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductModelInterface $associationOwner,
        ProductModelInterface $associationOwnerClone
    ) {
        $associationOwner->getCode()->willreturn('58');
        $associationOwnerClone->getCode()->willreturn('58');

        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwnerClone);

        $product->getAssociationForType($associationType)->willReturn($inversedAssociation);
        $inversedAssociation->getProductModels()->willReturn(new ArrayCollection([$associationOwner->getWrappedObject()]));

        $inversedAssociation->removeProductModel($associationOwner)->shouldBeCalled();
        $inversedAssociation->addProductModel($associationOwnerClone)->shouldBeCalled();

        $this->createInversedAssociation($association, $product);
    }

    function it_does_nothing_when_inversed_association_does_not_exist(
        AssociationInterface $association,
        AssociationTypeInterface $associationType,
        ProductInterface $associationOwner,
        ProductInterface $product,
        EntityManager $entityManager
    ) {
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);

        $product->getAssociationForType($associationType)->willReturn(null);
        $entityManager->persist()->shouldNotBeCalled();

        $this->removeInversedAssociation($association, $product);
    }

    function it_removes_inversed_association_when_association_with_product_exist(
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductInterface $associationOwner,
        ProductInterface $associatedProduct,
        EntityManager $entityManager
    ) {
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);
        $associatedProduct->getAssociationForType($associationType)->willReturn($inversedAssociation);

        $associatedProduct->removeAssociation($inversedAssociation)->shouldBeCalled();
        $inversedAssociation->removeProduct($associationOwner)->shouldBeCalled();
        $associatedProduct->addAssociation($inversedAssociation)->shouldBeCalled();

        $entityManager->persist($inversedAssociation)->shouldBeCalled();

        $this->removeInversedAssociation($association, $associatedProduct);
    }

    function it_removes_inversed_association_when_association_with_product_model_exist(
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductModelInterface $associationOwner,
        ProductInterface $product,
        EntityManager $entityManager
    ) {
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);
        $product->getAssociationForType($associationType)->willReturn($inversedAssociation);

        $inversedAssociation->removeProductModel($associationOwner)->shouldBeCalled();
        $entityManager->persist($inversedAssociation)->shouldBeCalled();

        $this->removeInversedAssociation($association, $product);
    }
}
