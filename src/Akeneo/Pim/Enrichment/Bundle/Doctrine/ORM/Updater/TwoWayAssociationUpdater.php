<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

class TwoWayAssociationUpdater implements TwoWayAssociationUpdaterInterface
{
    /** @var ManagerRegistry */
    private $registry;

    /** @var MissingAssociationAdder */
    private $missingAssociationAdder;

    public function __construct(
        ManagerRegistry $registry,
        MissingAssociationAdder $missingAssociationAdder
    ) {
        $this->registry = $registry;
        $this->missingAssociationAdder = $missingAssociationAdder;
    }

    /**
     * {@inheritdoc}
     */
    public function createInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $owner
    ): void {
        $associationType = $association->getAssociationType();
        $entityToAssociate = $association->getOwner();

        /** @var AssociationInterface $associationToUpdate */
        $associationToUpdate = $owner->getAssociationForType($associationType);
        if (null === $associationToUpdate) {
            $this->missingAssociationAdder->addMissingAssociations($owner);
            $associationToUpdate = $owner->getAssociationForType($associationType);
        }

        if ($entityToAssociate instanceof ProductInterface) {
            $this->addInversedAssociatedProduct($associationToUpdate, $entityToAssociate, $owner);
        } elseif ($entityToAssociate instanceof ProductModelInterface) {
            $this->addInversedAssociatedProductModel($associationToUpdate, $entityToAssociate);
        } else {
            throw new \LogicException(
                sprintf(
                    'Inversed associations are only for the classes "%s" and "%s". "%s" given.',
                    ProductInterface::class,
                    ProductModelInterface::class,
                    get_class($entityToAssociate)
                )
            );
        }
    }

    /**
     * In EE, products & associations are cloned for the Permission feature.
     * Because of that, sometimes, we will have in the association 2 differents instances of the same product
     * and Doctrine will throw an error saying it found a detached entity.
     * To fix this, we look for cloned objects by comparing the identifier.
     */
    private function addInversedAssociatedProduct(
        AssociationInterface $associationToUpdate,
        ProductInterface $associatedProduct,
        EntityWithAssociationsInterface $owner
    ): void {
        /** @var ProductInterface $product */
        foreach ($associationToUpdate->getProducts() as $product) {
            if ($product->getIdentifier() === $associatedProduct->getIdentifier() && $product !== $associatedProduct) {
                $associationToUpdate->removeProduct($product);
            }
        }

        $owner->removeAssociation($associationToUpdate);
        $associationToUpdate->addProduct($associatedProduct);
        $owner->addAssociation($associationToUpdate);
    }

    /**
     * In EE, products & associations are cloned for the Permission feature.
     * Because of that, sometimes, we will have in the association 2 differents instances of the same product model
     * and Doctrine will throw an error saying it found a detached entity.
     * To fix this, we look for cloned objects by comparing the identifier.
     */
    private function addInversedAssociatedProductModel(
        AssociationInterface $associationToUpdate,
        ProductModelInterface $associatedProductModel
    ): void {
        /** @var ProductModelInterface $productModel */
        foreach ($associationToUpdate->getProductModels() as $productModel) {
            if (
                $productModel->getCode() === $associatedProductModel->getCode() &&
                $productModel !== $associatedProductModel
            ) {
                $associationToUpdate->removeProductModel($productModel);
            }
        }

        $associationToUpdate->addProductModel($associatedProductModel);
    }

    /**
     * {@inheritdoc}
     */
    public function removeInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $owner
    ): void {
        $associationType = $association->getAssociationType();
        $associatedEntityToRemove = $association->getOwner();

        /** @var AssociationInterface $inversedAssociation */
        $inversedAssociation = $owner->getAssociationForType($associationType);
        if (null === $inversedAssociation) {
            return;
        }

        if ($associatedEntityToRemove instanceof ProductInterface) {
            $owner->removeAssociation($inversedAssociation);
            $inversedAssociation->removeProduct($associatedEntityToRemove);
            $owner->addAssociation($inversedAssociation);
        } elseif ($associatedEntityToRemove instanceof ProductModelInterface) {
            $inversedAssociation->removeProductModel($associatedEntityToRemove);
        } else {
            throw new \LogicException(
                sprintf(
                    'Inversed associations are only for the classes "%s" and "%s". "%s" given.',
                    ProductInterface::class,
                    ProductModelInterface::class,
                    get_class($associatedEntityToRemove)
                )
            );
        }

        /** @var ObjectManager $em */
        $em = $this->registry->getManager();
        $em->persist($inversedAssociation);
    }
}
