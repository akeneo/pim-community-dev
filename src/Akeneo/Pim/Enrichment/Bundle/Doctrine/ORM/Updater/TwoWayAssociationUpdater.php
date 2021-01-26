<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
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
     * In EE, products & associations are cloned for the Permission feature.
     * Because of that, sometimes, we will have in the association 2 differents instances of the same product or model
     * and Doctrine will throw an error saying it found a detached entity.
     * To fix this, we look for cloned objects by comparing the identifier.
     *
     * {@inheritdoc}
     */
    public function createInversedAssociation(
        $owner,
        string $associationTypeCode,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        if (!$associatedEntity->hasAssociationForTypeCode($associationTypeCode)) {
            $this->missingAssociationAdder->addMissingAssociations($associatedEntity);
        }
        if ($owner instanceof ProductInterface) {
            foreach ($associatedEntity->getAssociatedProducts($associationTypeCode) as $associatedProduct) {
                if ($associatedProduct->getIdentifier() === $owner->getIdentifier() && $associatedProduct !== $owner) {
                    $associatedEntity->removeAssociatedProduct($associatedProduct, $associationTypeCode);
                    break;
                }
            }
            $associatedEntity->addAssociatedProduct($owner, $associationTypeCode);
        } elseif ($owner instanceof ProductModelInterface) {
            foreach ($associatedEntity->getAssociatedProductModels($associationTypeCode) as $associatedProductModel) {
                if ($associatedProductModel->getCode() === $owner->getCode() && $associatedProductModel !== $owner) {
                    $associatedEntity->removeAssociatedProductModel($associatedProductModel, $associationTypeCode);
                    break;
                }
            }
            $associatedEntity->addAssociatedProductModel($owner, $associationTypeCode);
        } else {
            throw new \LogicException(
                sprintf(
                    'Inversed associations are only for the classes "%s" and "%s". "%s" given.',
                    ProductInterface::class,
                    ProductModelInterface::class,
                    get_class($associatedEntity)
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeInversedAssociation(
        $owner,
        string $associationTypeCode,
        EntityWithAssociationsInterface $associatedEntity
    ): void {
        if ($owner instanceof ProductInterface) {
            $associatedEntity->removeAssociatedProduct($owner, $associationTypeCode);
        } elseif ($owner instanceof ProductModelInterface) {
            $associatedEntity->removeAssociatedProductModel($owner, $associationTypeCode);
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
        $em->persist($associatedEntity);
    }
}
