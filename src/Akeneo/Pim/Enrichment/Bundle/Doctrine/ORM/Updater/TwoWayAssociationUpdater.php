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
     * {@inheritdoc}
     */
    public function createInversedAssociation(
        EntityWithAssociationsInterface $owner,
        string $associationTypeCode,
        $associatedEntity
    ): void {
        if (!$associatedEntity->hasAssociationForTypeCode($associationTypeCode)) {
            $this->missingAssociationAdder->addMissingAssociations($associatedEntity);
        }
        if ($owner instanceof ProductInterface) {
            $associatedEntity->addAssociatedProduct($owner, $associationTypeCode);
        } elseif ($owner instanceof ProductModelInterface) {
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
        EntityWithAssociationsInterface $owner,
        string $associationTypeCode,
        $associatedEntity
    ): void {
        if (!$associatedEntity->hasAssociationForTypeCode($associationTypeCode)) {
            $this->missingAssociationAdder->addMissingAssociations($associatedEntity);
        }
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
