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
     * @inheritdoc
     */
    public function createInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        $associationType = $association->getAssociationType();
        $owner = $association->getOwner();

        /** @var AssociationInterface $inversedAssociation */
        $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        if (null === $inversedAssociation) {
            $this->missingAssociationAdder->addMissingAssociations($associatedEntity);
            $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        }

        if ($owner instanceof ProductInterface) {
            $this->addInversedAssociatedProduct($inversedAssociation, $owner, $associatedEntity);
        } elseif ($owner instanceof ProductModelInterface) {
            $this->addInversedAssociatedProductModel($inversedAssociation, $owner);
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
    }

    /**
     * In EE, products & associations are cloned for the Permission feature.
     * Because of that, sometimes, we will have in the association 2 differents instances of the same product
     * and Doctrine will throw an error saying it found a detached entity.
     * To fix this, we look for cloned objects by comparing the identifier.
     */
    private function addInversedAssociatedProduct(
        AssociationInterface $association,
        ProductInterface $owner,
        EntityWithAssociationsInterface $associatedProduct
    ): void {
        /** @var ProductInterface $product */
        foreach ($association->getProducts() as $product) {
            if ($product->getIdentifier() === $owner->getIdentifier()
                && $product !== $owner) {
                $association->removeProduct($product);
            }
        }

        $associatedProduct->removeAssociation($association);
        $association->addProduct($owner);
        $associatedProduct->addAssociation($association);
    }

    /**
     * In EE, products & associations are cloned for the Permission feature.
     * Because of that, sometimes, we will have in the association 2 differents instances of the same product model
     * and Doctrine will throw an error saying it found a detached entity.
     * To fix this, we look for cloned objects by comparing the identifier.
     */
    private function addInversedAssociatedProductModel(
        AssociationInterface $association,
        ProductModelInterface $associatedProductModel
    ): void {
        /** @var ProductModelInterface $productModel */
        foreach ($association->getProductModels() as $productModel) {
            if ($productModel->getCode() === $associatedProductModel->getCode()
                && $productModel !== $associatedProductModel) {
                $association->removeProductModel($productModel);
            }
        }

        $association->addProductModel($associatedProductModel);
    }

    /**
     * @inheritdoc
     */
    public function removeInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        $associationType = $association->getAssociationType();
        $owner = $association->getOwner();

        /** @var AssociationInterface $inversedAssociation */
        $inversedAssociation = $associatedEntity->getAssociationForType($associationType);
        if (null === $inversedAssociation) {
            return;
        }

        if ($owner instanceof ProductInterface) {
            $associatedEntity->removeAssociation($inversedAssociation);
            $inversedAssociation->removeProduct($owner);
            $associatedEntity->addAssociation($inversedAssociation);
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

        /** @var ObjectManager $em */
        $em = $this->registry->getManager();
        $em->persist($inversedAssociation);
    }
}
