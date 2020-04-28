<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    function it_create_missing_association_on_reverse_association_when_missing(
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $product,
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductInterface $associationOwner,
        EntityManager $entityManager
    ) {
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);

        $product->getAssociationForType($associationType)->willReturn(null, $inversedAssociation);
        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

        $inversedAssociation->addProduct($associationOwner)->shouldBeCalled();
        $entityManager->persist($inversedAssociation);

        $this->createInversedAssociation($association, $product);
    }


    function it_add_product_model_on_inversed_association_when_owner_is_product_model(
        ProductInterface $product,
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductModelInterface $associationOwner,
        EntityManager $entityManager
    ) {
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);

        $product->getAssociationForType($associationType)->willReturn($inversedAssociation);

        $inversedAssociation->addProductModel($associationOwner)->shouldBeCalled();
        $entityManager->persist($inversedAssociation);

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

    function it_remove_inversed_association_when_association_with_product_exist(
        AssociationInterface $association,
        AssociationInterface $inversedAssociation,
        AssociationTypeInterface $associationType,
        ProductInterface $associationOwner,
        ProductInterface $product,
        EntityManager $entityManager
    ) {
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($associationOwner);
        $product->getAssociationForType($associationType)->willReturn($inversedAssociation);

        $inversedAssociation->removeProduct($associationOwner)->shouldBeCalled();
        $entityManager->persist($inversedAssociation)->shouldBeCalled();

        $this->removeInversedAssociation($association, $product);
    }

    function it_remove_inversed_association_when_association_with_product_model_exist(
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
