<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Association;

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

    private function removeInvertedProductModelAssociationsDeleted(
        ProductInterface $product,
        array $twoWayAssociationTypeIds
    ): void {
        $query = <<<SQL
DELETE FROM pim_catalog_association_product_model_to_product
WHERE association_id IN (
    SELECT * FROM (
        SELECT product_model_association.id
        FROM pim_catalog_product_model_association product_model_association
        INNER JOIN pim_catalog_association_product_model_to_product product_model_association_with_product
            ON product_model_association_with_product.association_id = product_model_association.id
        WHERE association_type_id IN (:association_type_ids)
        AND product_id = :owner_id
        AND (association_type_id, product_id, owner_id) NOT IN (
            SELECT existing_product_association.association_type_id, existing_product_association.owner_id, existing_product_association_with_product_model.product_model_id
            FROM pim_catalog_association existing_product_association
            INNER JOIN pim_catalog_association_product_model existing_product_association_with_product_model
                ON existing_product_association_with_product_model.association_id = existing_product_association.id
        )
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

    private function removeInvertedProductAssociationsDeleted(
        ProductInterface $product,
        array $twoWayAssociationTypeIds
    ): void {
        $query = <<<SQL
DELETE FROM pim_catalog_association_product
WHERE association_id IN (
    SELECT * FROM (
        SELECT product_association.id
        FROM pim_catalog_association product_association
        INNER JOIN pim_catalog_association_product product_association_with_product
            ON product_association_with_product.association_id = product_association.id
        WHERE association_type_id IN (:association_type_ids)
        AND product_id = :owner_id
        AND (association_type_id, product_id, owner_id) NOT IN (
            SELECT existing_product_association.association_type_id, existing_product_association.owner_id, existing_product_association_with_product.product_id
            FROM pim_catalog_association existing_product_association
            INNER JOIN pim_catalog_association_product existing_product_association_with_product
                ON existing_product_association_with_product.association_id = existing_product_association.id
        )
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
SELECT product_association.association_type_id, product_association_with_product_model.product_model_id
FROM pim_catalog_association product_association
JOIN pim_catalog_association_product_model product_association_with_product_model
    ON product_association_with_product_model.association_id = product_association.id
LEFT JOIN pim_catalog_product_model_association existing_product_model_association
    ON existing_product_model_association.association_type_id = product_association.association_type_id
    AND existing_product_model_association.owner_id = product_association_with_product_model.product_model_id
WHERE product_association.owner_id = :owner_id
AND product_association.association_type_id IN (:association_type_ids)
AND existing_product_model_association.owner_id IS NULL
AND existing_product_model_association.association_type_id IS NULL;
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
SELECT product_association.association_type_id, product_association_with_product.product_id
FROM pim_catalog_association product_association
JOIN pim_catalog_association_product product_association_with_product
    ON product_association_with_product.association_id = product_association.id
LEFT JOIN pim_catalog_association existing_product_association
    ON existing_product_association.association_type_id = product_association.association_type_id
    AND existing_product_association.owner_id = product_association_with_product.product_id
WHERE product_association.owner_id = :owner_id
AND product_association.association_type_id IN (:association_type_ids)
AND existing_product_association.owner_id IS NULL
AND existing_product_association.association_type_id IS NULL;
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
SELECT existing_product_association.id, :owner_id
FROM pim_catalog_association product_association
JOIN pim_catalog_association_product product_association_with_product
    ON product_association_with_product.association_id = product_association.id
JOIN pim_catalog_association existing_product_association
    ON existing_product_association.association_type_id = product_association.association_type_id
    AND existing_product_association.owner_id = product_association_with_product.product_id
LEFT OUTER JOIN pim_catalog_association_product existing_product_association_with_product
    ON existing_product_association.id = existing_product_association_with_product.association_id
WHERE product_association.owner_id = :owner_id
AND product_association.association_type_id IN (:association_type_ids)
AND existing_product_association_with_product.association_id IS NULL;
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
SELECT existing_product_model_association.id, :owner_id
FROM pim_catalog_association product_association
JOIN pim_catalog_association_product_model product_association_with_product_model
    ON product_association_with_product_model.association_id = product_association.id
JOIN pim_catalog_product_model_association existing_product_model_association
    ON existing_product_model_association.association_type_id = product_association.association_type_id
    AND existing_product_model_association.owner_id = product_association_with_product_model.product_model_id
LEFT OUTER JOIN pim_catalog_association_product_model_to_product existing_product_model_association_with_product
    ON existing_product_model_association.id = existing_product_model_association_with_product.association_id
WHERE product_association.owner_id = :owner_id
AND product_association.association_type_id IN (:association_type_ids)
AND existing_product_model_association_with_product.association_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_type_ids' => $twoWayAssociationTypeIds, 'owner_id' => $product->getId()],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }
}
