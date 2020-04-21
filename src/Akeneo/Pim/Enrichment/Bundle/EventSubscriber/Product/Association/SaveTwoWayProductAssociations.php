<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveTwoWayProductAssociations implements EventSubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'saveInvertedAssociations'
        ];
    }

    public function saveInvertedAssociations(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $twoWayAssociations = array_filter($product->getAssociations()->toArray(), function (AssociationInterface $association) {
            return $association->getAssociationType()->isTwoWay();
        });

        if (empty($twoWayAssociations)) {
            return;
        }

        $twoWayAssociationTypeIds = array_map(function (AssociationInterface $association) {
            return $association->getAssociationType()->getId();
        }, $twoWayAssociations);

        try {
            $this->connection->beginTransaction();

            $this->saveInvertedProductAssociation($product, $twoWayAssociationTypeIds);
            $this->saveInvertedProductModelAssociation($product, $twoWayAssociationTypeIds);

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    private function saveInvertedProductAssociation(ProductInterface $product, array $twoWayAssociationTypeIds)
    {
        $this->removeInvertedProductAssociationsDeleted($product, $twoWayAssociationTypeIds);
        $this->saveNewInvertedAssociations($product, $twoWayAssociationTypeIds);
        $this->saveNewInvertedProductAssociations($product, $twoWayAssociationTypeIds);
    }

    private function saveInvertedProductModelAssociation(ProductInterface $product, array $twoWayAssociationTypeIds)
    {
        $this->removeInvertedProductModelAssociationsDeleted($product, $twoWayAssociationTypeIds);
        $this->saveNewInvertedProductModelAssociations($product, $twoWayAssociationTypeIds);
        $this->saveNewInvertedProductModelToProductAssociations($product, $twoWayAssociationTypeIds);
    }

    /**
     * Remove all product two way associations associated with the current product whose current product is not associated.
     * At this time if Julia remove Product A association with Product B, Doctrine remove A => B association.
     * In this query we remove B => A association by identifying two way association that doesn't have inverted association with the current product
     * @param ProductInterface $product
     * @param array $twoWayAssociationTypeIds
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeInvertedProductModelAssociationsDeleted(
        ProductInterface $product,
        array $twoWayAssociationTypeIds
    ): void {
        $query = <<<SQL
DELETE FROM pim_catalog_association_product_model_to_product
WHERE (association_id, product_id) IN (
    SELECT id, product_id FROM (
        SELECT pma.id, product_id
        FROM pim_catalog_product_model_association pma
        INNER JOIN pim_catalog_association_product_model_to_product apmtp
            ON apmtp.association_id = pma.id
        WHERE NOT EXISTS (
            SELECT existing_a.id
            FROM pim_catalog_association existing_a
            INNER JOIN pim_catalog_association_product_model existing_apm
                ON existing_apm.association_id = existing_a.id
            WHERE existing_a.association_type_id = pma.association_type_id
            AND existing_a.owner_id = apmtp.product_id
            AND existing_apm.product_model_id = pma.owner_id
        )
        AND association_type_id IN (:association_type_ids)
        AND product_id = :owner_id
    ) as association_id_to_delete
);
SQL;

        $this->connection->executeUpdate(
            $query,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $product->getId()
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    /**
     * Remove all product model two way associations associated with the current product whose current product is not associated.
     * At this time if Julia remove Product A association with Product model B, Doctrine remove A => B association.
     * In this query we remove B => A association by identifying two way association that doesn't have inverted association with the current product
     * @param ProductInterface $product
     * @param array $twoWayAssociationTypeIds
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeInvertedProductAssociationsDeleted(
        ProductInterface $product,
        array $twoWayAssociationTypeIds
    ): void {
        $query = <<<SQL
DELETE FROM pim_catalog_association_product
WHERE (association_id, product_id) IN (
    SELECT id, product_id FROM (
        SELECT a.id, ap.product_id
        FROM pim_catalog_association a
        INNER JOIN pim_catalog_association_product ap
            ON ap.association_id = a.id
        WHERE NOT EXISTS (
            SELECT existing_a.id
            FROM pim_catalog_association existing_a
            INNER JOIN pim_catalog_association_product existing_ap
                ON existing_ap.association_id = existing_a.id
            WHERE a.association_type_id = existing_a.association_type_id
            AND ap.product_id = existing_a.owner_id
            AND a.owner_id = existing_ap.product_id
        )
        AND association_type_id IN (:association_type_ids)
        AND product_id = :owner_id
    ) as association_id_to_delete
);
SQL;

        $this->connection->executeUpdate(
            $query,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $product->getId()
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewInvertedProductModelAssociations(
        ProductInterface $product,
        array $twoWayAssociationTypeIds
    ): void {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_product_model_association (association_type_id, owner_id)
SELECT a.association_type_id, apm.product_model_id
FROM pim_catalog_association a
JOIN pim_catalog_association_product_model apm
    ON apm.association_id = a.id
LEFT JOIN pim_catalog_product_model_association existing_pma
    ON existing_pma.association_type_id = a.association_type_id
    AND existing_pma.owner_id = apm.product_model_id
WHERE a.owner_id = :owner_id
AND a.association_type_id IN (:association_type_ids)
AND existing_pma.owner_id IS NULL
AND existing_pma.association_type_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $product->getId()
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewInvertedAssociations(ProductInterface $product, array $twoWayAssociationTypeIds): void
    {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_association (association_type_id, owner_id)
SELECT a.association_type_id, ap.product_id
FROM pim_catalog_association a
JOIN pim_catalog_association_product ap
    ON ap.association_id = a.id
LEFT JOIN pim_catalog_association existing_a
    ON existing_a.association_type_id = a.association_type_id
    AND existing_a.owner_id = ap.product_id
WHERE a.owner_id = :owner_id
AND a.association_type_id IN (:association_type_ids)
AND existing_a.owner_id IS NULL
AND existing_a.association_type_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $product->getId()
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewInvertedProductAssociations(
        ProductInterface $product,
        array $twoWayAssociationTypeIds
    ): void {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product (association_id, product_id)
SELECT existing_a.id, :owner_id
FROM pim_catalog_association a
JOIN pim_catalog_association_product ap
    ON ap.association_id = a.id
JOIN pim_catalog_association existing_a
    ON existing_a.association_type_id = a.association_type_id
	AND existing_a.owner_id = ap.product_id
WHERE a.owner_id = :owner_id
AND a.association_type_id IN (:association_type_ids)
AND NOT EXISTS (
	SELECT *
    FROM pim_catalog_association_product existing_ap
    WHERE existing_ap.association_id = existing_a.id
    AND existing_ap.product_id = :owner_id
);
SQL;

        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_type_ids' => $twoWayAssociationTypeIds, 'owner_id' => $product->getId()],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewInvertedProductModelToProductAssociations(
        ProductInterface $product,
        array $twoWayAssociationTypeIds
    ): void {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product_model_to_product (association_id, product_id)
SELECT DISTINCT existing_pma.id, :owner_id
FROM pim_catalog_association a
JOIN pim_catalog_association_product_model apm
    ON apm.association_id = a.id
JOIN pim_catalog_product_model_association existing_pma
    ON existing_pma.association_type_id = a.association_type_id
    AND existing_pma.owner_id = apm.product_model_id
WHERE a.owner_id = :owner_id
AND a.association_type_id IN (:association_type_ids)
AND NOT EXISTS (
    SELECT *
    FROM pim_catalog_association_product_model_to_product existing_apmtp
    WHERE existing_apmtp.association_id = existing_pma.id
    AND existing_apmtp.product_id = :owner_id
);
SQL;

        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_type_ids' => $twoWayAssociationTypeIds, 'owner_id' => $product->getId()],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }
}
