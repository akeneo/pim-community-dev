<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Doctrine\DBAL\Connection;

class TwoWayProductModelAssociationsSaver
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function saveInvertedAssociations(ProductModelInterface $productModel, array $associations)
    {
        $twoWayAssociations = array_filter($associations, function (AssociationInterface $association) {
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

            $this->saveInvertedProductAssociation($productModel, $twoWayAssociationTypeIds);
            $this->saveInvertedProductModelAssociation($productModel, $twoWayAssociationTypeIds);

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    private function saveInvertedProductAssociation(ProductModelInterface $productModel, array $twoWayAssociationTypeIds)
    {
        $this->removeInvertedProductAssociationsDeleted($productModel, $twoWayAssociationTypeIds);
        $this->saveNewInvertedAssociations($productModel, $twoWayAssociationTypeIds);
        $this->saveNewInvertedProductAssociations($productModel, $twoWayAssociationTypeIds);
    }

    private function saveInvertedProductModelAssociation(ProductModelInterface $productModel, array $twoWayAssociationTypeIds)
    {
        $this->removeInvertedProductModelAssociationsDeleted($productModel, $twoWayAssociationTypeIds);
        $this->saveNewInvertedProductModelAssociations($productModel, $twoWayAssociationTypeIds);
        $this->saveNewInvertedProductModelToProductAssociations($productModel, $twoWayAssociationTypeIds);
    }

    /**
     * Remove all product two way associations associated with the current product model whose current product model is not associated.
     * At this time if Julia remove Model Product A association with Product B, Doctrine remove A => B association.
     * In this query we remove B => A association by identifying two way association that doesn't have inverted association with the current product model
     * @param ProductModelInterface $productModel
     * @param array $twoWayAssociationTypeIds
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeInvertedProductAssociationsDeleted(
        ProductModelInterface $productModel,
        array $twoWayAssociationTypeIds
    ): void {
        $query = <<<SQL
DELETE FROM pim_catalog_association_product_model
WHERE (association_id, product_model_id) IN (
    SELECT id, product_model_id FROM (
        SELECT a.id, product_model_id
        FROM pim_catalog_association a
        INNER JOIN pim_catalog_association_product_model apm
            ON apm.association_id = a.id
        WHERE NOT EXISTS (
            SELECT existing_pma.id
            FROM pim_catalog_product_model_association existing_pma
            INNER JOIN pim_catalog_association_product_model_to_product existing_apmtp
                ON existing_apmtp.association_id = existing_pma.id
            WHERE a.association_type_id = existing_pma.association_type_id
            AND apm.product_model_id = existing_pma.owner_id
            AND a.owner_id = existing_apmtp.product_id
        )
        AND association_type_id IN (:association_type_ids)
        AND product_model_id = :owner_id
    ) as association_id_to_delete
);
SQL;

        $this->connection->executeUpdate(
            $query,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $productModel->getId()
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    /**
     * Remove all product model two way associations associated with the current product model whose current product model is not associated.
     * At this time if Julia remove Product model A association with Product model B, Doctrine remove A => B association.
     * In this query we remove B => A association by identifying two way association that doesn't have inverted association with the current product model
     * @param ProductModelInterface $productModel
     * @param array $twoWayAssociationTypeIds
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeInvertedProductModelAssociationsDeleted(
        ProductModelInterface $productModel,
        array $twoWayAssociationTypeIds
    ): void {
        $query = <<<SQL
DELETE FROM pim_catalog_association_product_model_to_product_model
WHERE (association_id, product_model_id) IN (
    SELECT id, product_model_id FROM (
        SELECT pma.id, product_model_id
        FROM pim_catalog_product_model_association pma
        INNER JOIN pim_catalog_association_product_model_to_product_model apmtpm
            ON apmtpm.association_id = pma.id
        WHERE NOT EXISTS (
            SELECT existing_pma.id
            FROM pim_catalog_product_model_association existing_pma
            INNER JOIN pim_catalog_association_product_model_to_product_model existing_apmtpm
                ON existing_apmtpm.association_id = existing_pma.id
            WHERE existing_pma.association_type_id = pma.association_type_id
            AND existing_pma.owner_id = apmtpm.product_model_id
            AND existing_apmtpm.product_model_id = pma.owner_id
        )
        AND association_type_id IN (:association_type_ids)
        AND product_model_id = :owner_id
    ) as association_id_to_delete
);
SQL;

        $this->connection->executeUpdate(
            $query,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $productModel->getId()
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewInvertedAssociations(ProductModelInterface $productModel, array $twoWayAssociationTypeIds): void
    {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_association (association_type_id, owner_id)
SELECT pma.association_type_id, apmtp.product_id
FROM pim_catalog_product_model_association pma
JOIN pim_catalog_association_product_model_to_product apmtp
    ON apmtp.association_id = pma.id
LEFT JOIN pim_catalog_association existing_a
    ON existing_a.association_type_id = pma.association_type_id
    AND existing_a.owner_id = apmtp.product_id
WHERE pma.owner_id = :owner_id
AND pma.association_type_id IN (:association_type_ids)
AND existing_a.owner_id IS NULL
AND existing_a.association_type_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $productModel->getId()
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewInvertedProductModelAssociations(
        ProductModelInterface $productModel,
        array $twoWayAssociationTypeIds
    ): void {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_product_model_association (association_type_id, owner_id)
SELECT pma.association_type_id, apmtpm.product_model_id
FROM pim_catalog_product_model_association pma
JOIN pim_catalog_association_product_model_to_product_model apmtpm
    ON apmtpm.association_id = pma.id
LEFT JOIN pim_catalog_product_model_association existing_pma
    ON existing_pma.association_type_id = pma.association_type_id
    AND existing_pma.owner_id = apmtpm.product_model_id
WHERE pma.owner_id = :owner_id
AND pma.association_type_id IN (:association_type_ids)
AND existing_pma.owner_id IS NULL
AND existing_pma.association_type_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $productModel->getId()
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewInvertedProductAssociations(
        ProductModelInterface $productModel,
        array $twoWayAssociationTypeIds
    ): void {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product_model (association_id, product_model_id)
SELECT existing_a.id, :owner_id
FROM pim_catalog_product_model_association pma
JOIN pim_catalog_association_product_model_to_product apmtp
    ON apmtp.association_id = pma.id
JOIN pim_catalog_association existing_a
    ON existing_a.association_type_id = pma.association_type_id
	AND existing_a.owner_id = apmtp.product_id
WHERE pma.owner_id = :owner_id
AND pma.association_type_id IN (:association_type_ids)
AND NOT EXISTS (
	SELECT *
    FROM pim_catalog_association_product_model existing_apm
    WHERE existing_apm.association_id = existing_a.id
    AND existing_apm.product_model_id = :owner_id
);
SQL;

        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_type_ids' => $twoWayAssociationTypeIds, 'owner_id' => $productModel->getId()],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewInvertedProductModelToProductAssociations(
        ProductModelInterface $productModel,
        array $twoWayAssociationTypeIds
    ): void {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product_model_to_product_model (association_id, product_model_id)
SELECT DISTINCT existing_pma.id, :owner_id
FROM pim_catalog_product_model_association pma
JOIN pim_catalog_association_product_model_to_product_model apmtpm
    ON apmtpm.association_id = pma.id
JOIN pim_catalog_product_model_association existing_pma
    ON existing_pma.association_type_id = pma.association_type_id
    AND existing_pma.owner_id = apmtpm.product_model_id
WHERE pma.owner_id = :owner_id
AND pma.association_type_id IN (:association_type_ids)
AND NOT EXISTS (
    SELECT *
    FROM pim_catalog_association_product_model_to_product_model existing_apmtpm
    WHERE existing_apmtpm.association_id = existing_pma.id
    AND existing_apmtpm.product_model_id = :owner_id
);
SQL;

        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_type_ids' => $twoWayAssociationTypeIds, 'owner_id' => $productModel->getId()],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }
}
