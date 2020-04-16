<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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

        $twoWayAssociations = array_filter($product->getAssociations()->toArray(), function(AssociationInterface $association) {
            return $association->getAssociationType()->isTwoWay();
        });

        if (empty($twoWayAssociations)) {
            return;
        }

        $twoWayAssociationTypeIds = array_map(function(AssociationInterface $association) {
            return $association->getAssociationType()->getId();
        }, $twoWayAssociations);

        try {
            $this->connection->beginTransaction();
            foreach ($twoWayAssociations as $twoWayAssociation) {
                $this->removeInvertedProductAssociationsDeleted($twoWayAssociation, $product);
                $this->removeInvertedProductModelAssociationsDeleted($twoWayAssociation, $product);
            }

            $this->saveNewAssociation($twoWayAssociationTypeIds, $product->getId());
            $this->saveProductAssociation($twoWayAssociationTypeIds, $product->getId());

            $this->saveNewProductModelAssociation($twoWayAssociationTypeIds, $product->getId());
            $this->saveProductModelAssociation($twoWayAssociationTypeIds, $product->getId());

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    private function removeInvertedProductModelAssociationsDeleted(
        AssociationInterface $association,
        ProductInterface $product
    ): void {
        $ownerProductModelIds = $association->getProductModels()->map(
            function (ProductModelInterface $ownerProductModel) {
                return $ownerProductModel->getId();
            }
        );

        $types = [];
        $params = [
            'association_type_id' => $association->getAssociationType()->getId(),
            'product_id' => $product->getId(),
        ];

        $query = <<<SQL
DELETE FROM pim_catalog_association_product_model_to_product
WHERE association_id IN (
	SELECT * FROM (
		SELECT a.id
	    FROM pim_catalog_product_model_association a INNER JOIN pim_catalog_association_product_model_to_product ap ON ap.association_id = a.id
	    WHERE association_type_id = :association_type_id AND product_id = :product_id
	) as association_id_to_delete
)
SQL;

        if (!$ownerProductModelIds->isEmpty()) {
            $query = <<<SQL
DELETE FROM pim_catalog_association_product_model_to_product
WHERE association_id IN (
	SELECT * FROM (
		SELECT a.id
	    FROM pim_catalog_product_model_association a INNER JOIN pim_catalog_association_product_model_to_product ap ON ap.association_id = a.id
	    WHERE association_type_id = :association_type_id AND product_id = :product_id AND owner_id NOT IN (:owner_product_model_ids_formatted)
	) as association_id_to_delete
)
SQL;
            $params['owner_product_model_ids_formatted'] = $ownerProductModelIds->toArray();
            $types['owner_product_model_ids_formatted'] = Connection::PARAM_INT_ARRAY;
        }

        $this->connection->executeUpdate($query, $params, $types);
    }

    private function removeInvertedProductAssociationsDeleted(
        AssociationInterface $association,
        ProductInterface $product
    ): void {
        $ownerProductIds = $association->getProducts()->map(
            function (ProductInterface $ownerProduct) {
                return $ownerProduct->getId();
            }
        );

        $types = [];
        $params = [
            'association_type_id' => $association->getAssociationType()->getId(),
            'product_id' => $product->getId(),
        ];

        $query = <<<SQL
DELETE FROM pim_catalog_association_product
WHERE association_id IN (
	SELECT * FROM (
		SELECT a.id
	    FROM pim_catalog_association a INNER JOIN pim_catalog_association_product ap ON ap.association_id = a.id
	    WHERE association_type_id = :association_type_id AND product_id = :product_id
	) as association_id_to_delete
)
SQL;

        if (!$ownerProductIds->isEmpty()) {
            $query = <<<SQL
DELETE FROM pim_catalog_association_product
WHERE association_id IN (
	SELECT * FROM (
		SELECT a.id
	    FROM pim_catalog_association a INNER JOIN pim_catalog_association_product ap ON ap.association_id = a.id
	    WHERE association_type_id = :association_type_id AND product_id = :product_id AND owner_id NOT IN (:owner_product_ids_formatted)
	) as association_id_to_delete
)
SQL;
            $params['owner_product_ids_formatted'] = $ownerProductIds->toArray();
            $types['owner_product_ids_formatted'] = Connection::PARAM_INT_ARRAY;
        }

        $this->connection->executeUpdate($query, $params, $types);
    }

    private function saveNewProductModelAssociation(array $twoWayAssociationTypeIds, int $ownerProductId): void
    {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_product_model_association (association_type_id, owner_id)
SELECT pca.association_type_id, pcapm.product_model_id
FROM pim_catalog_association pca
JOIN pim_catalog_association_product_model pcapm
    ON pcapm.association_id = pca.id
LEFT JOIN pim_catalog_product_model_association existing_association
    ON existing_association.association_type_id = pca.association_type_id
    AND existing_association.owner_id = pcapm.product_model_id
WHERE pca.owner_id = :owner_id
AND pca.association_type_id IN (:association_type_ids)
AND existing_association.owner_id IS NULL
AND existing_association.association_type_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $ownerProductId
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveNewAssociation(array $twoWayAssociationTypeIds, int $ownerProductId): void
    {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_association (association_type_id, owner_id)
SELECT pca.association_type_id, pcap.product_id
FROM pim_catalog_association pca
JOIN pim_catalog_association_product pcap
    ON pcap.association_id = pca.id
LEFT JOIN pim_catalog_association existing_association
    ON existing_association.association_type_id = pca.association_type_id
    AND existing_association.owner_id = pcap.product_id
WHERE pca.owner_id = :owner_id
AND pca.association_type_id IN (:association_type_ids)
AND existing_association.owner_id IS NULL
AND existing_association.association_type_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_ids' => $twoWayAssociationTypeIds,
                'owner_id' => $ownerProductId
            ],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveProductAssociation(array $twoWayAssociationTypeIds, int $ownerProductId): void
    {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product (association_id, product_id)
SELECT existing_association.id, :owner_id
FROM pim_catalog_association pca
JOIN pim_catalog_association_product pcap
    ON pcap.association_id = pca.id
JOIN pim_catalog_association existing_association
    ON existing_association.association_type_id = pca.association_type_id
    AND existing_association.owner_id = pcap.product_id
LEFT OUTER JOIN pim_catalog_association_product existing_product_association
    ON existing_association.id = existing_product_association.association_id
WHERE pca.owner_id = :owner_id
AND pca.association_type_id IN (:association_type_ids)
AND existing_product_association.association_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_type_ids' => $twoWayAssociationTypeIds, 'owner_id' => $ownerProductId],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function saveProductModelAssociation(array $twoWayAssociationTypeIds, int $ownerProductId): void
    {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product_model_to_product (association_id, product_id)
SELECT existing_association.id, :owner_id
FROM pim_catalog_association pca
JOIN pim_catalog_association_product_model pcapm
    ON pcapm.association_id = pca.id
JOIN pim_catalog_product_model_association existing_association
    ON existing_association.association_type_id = pca.association_type_id
    AND existing_association.owner_id = pcapm.product_model_id
LEFT OUTER JOIN pim_catalog_association_product_model_to_product existing_product_association
ON existing_association.id = existing_product_association.association_id
WHERE pca.owner_id = :owner_id
AND pca.association_type_id IN (:association_type_ids)
AND existing_product_association.association_id IS NULL;
SQL;

        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_type_ids' => $twoWayAssociationTypeIds, 'owner_id' => $ownerProductId],
            ['association_type_ids' => Connection::PARAM_INT_ARRAY]
        );
    }
}
