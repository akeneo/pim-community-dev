<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

class AssociationUpdater
{
    /** @var ManagerRegistry */
    private $registry;

    private $missingAssociationAdder;

    public function __construct(ManagerRegistry $registry, MissingAssociationAdder $missingAssociationAdder)
    {
        $this->registry = $registry;
        $this->missingAssociationAdder = $missingAssociationAdder;
    }

    /**
     * An association has been created, this method should create the inversed association.
     * This is used by two-way associations.
     *
     * @param AssociationInterface $association
     * @param EntityWithAssociationsInterface $associatedEntity
     */
    public function createInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        /** @var ObjectManager $em */
        $em = $this->registry->getManager();
        $associationType = $association->getAssociationType();
        $owner = $association->getOwner();

        /** @var AssociationInterface $inversedAssociation */
        $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        if (null === $inversedAssociation) {
            $this->missingAssociationAdder->addMissingAssociations($associatedEntity);
            $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        }

        if ($owner instanceof ProductInterface) {
            $inversedAssociation->addProduct($owner);
        } elseif ($owner instanceof ProductModelInterface) {
            $inversedAssociation->addProductModel($owner);
        } else {
            throw new \LogicException(
                sprintf(
                    'Inversed associations are only for the classes "%s" and "%s". "%s" given.',
                    ProductInterface::class,
                    ProductModelInterface::class,
                    get_class($owner)
                )
            );
        }

        $em->persist($inversedAssociation);
    }

    /**
     * An association has been removed, this method should remove the inversed association if there is one.
     * This is used by two-way associations.
     *
     * @param AssociationInterface $association
     * @param EntityWithAssociationsInterface $associatedEntity
     */
    public function removeInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        /** @var ObjectManager $em */
        $em = $this->registry->getManager();
        $owner = $association->getOwner();
        $associationType = $association->getAssociationType();

        /** @var AssociationInterface $inversedAssociation */
        $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        if (null === $inversedAssociation) {
            return;
        }

        if ($owner instanceof ProductInterface) {
            $inversedAssociation->removeProduct($owner);
        } elseif ($owner instanceof ProductModelInterface) {
            $inversedAssociation->removeProductModel($owner);
        } else {
            throw new \LogicException(
                sprintf(
                    'Inversed associations are only for the classes "%s" and "%s". "%s" given.',
                    ProductInterface::class,
                    ProductModelInterface::class,
                    get_class($owner)
                )
            );
        }

        $em->persist($inversedAssociation);
    }
}
