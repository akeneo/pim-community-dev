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

        foreach ($product->getAssociations() as $association) {
            if ($association->getAssociationType()->isTwoWay()) {
                $this->connection->transactional(function() use ($product, $association) {
                    $this->removeInvertedProductAssociationsDeleted($association, $product);
                    $this->removeInvertedProductModelAssociationsDeleted($association, $product);

                    $this->saveTwoWayProductAssociation($association, $product->getId());
                    $this->saveTwoWayProductModelAssociation($association, $product->getId());
                });
            }
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

    private function saveTwoWayProductAssociation(
        AssociationInterface $productAssociation,
        int $ownerProductId
    ): void {
        foreach ($productAssociation->getProducts() as $product) {
            $this->saveNewAssociation($productAssociation, $product->getId());
            $newAssociationId = $this->fetchAssociationId(
                $productAssociation->getAssociationType()->getId(),
                $product->getId()
            );
            $this->saveProductAssociation($newAssociationId, $ownerProductId);
        }
    }
    private function saveTwoWayProductModelAssociation(
        AssociationInterface $productAssociation,
        int $ownerProductId
    ): void {
        foreach ($productAssociation->getProductModels() as $productModel) {
            $this->saveNewProductModelAssociation($productAssociation, $productModel->getId());
            $newAssociationId = $this->fetchProductModelAssociationId(
                $productAssociation->getAssociationType()->getId(),
                $productModel->getId()
            );
            $this->saveProductModelAssociation($newAssociationId, $ownerProductId);
        }
    }


    private function saveNewProductModelAssociation(AssociationInterface $productAssociation, int $productModelId): void
    {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_product_model_association (association_type_id, owner_id)
VALUES
(:association_type_id, :product_model_id)
ON DUPLICATE KEY UPDATE
    association_type_id = :association_type_id,
    owner_id = :product_model_id
SQL;
        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_id' => $productAssociation->getAssociationType()->getId(),
                'product_model_id'          => $productModelId
            ]
        );
    }

    private function saveNewAssociation(AssociationInterface $productAssociation, int $productId): void
    {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_association (association_type_id, owner_id)
VALUES
(:association_type_id, :product_id)
ON DUPLICATE KEY UPDATE
    association_type_id = :association_type_id,
    owner_id = :product_id
SQL;
        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_id' => $productAssociation->getAssociationType()->getId(),
                'product_id'          => $productId
            ]
        );
    }

    private function fetchProductModelAssociationId(int $associationId, int $productModelId): int
    {
        $stmt = $this->connection->executeQuery(
            'SELECT id FROM pim_catalog_product_model_association WHERE owner_id=:owner_id AND association_type_id=:association_type_id',
            ['owner_id' => $productModelId, 'association_type_id' => $associationId]
        );
        $result = $stmt->fetch(\PDO::FETCH_COLUMN);
        if (!$result) {
            throw new \LogicException('Something went wrong');
        }

        return (int)$result;
    }

    private function fetchAssociationId(int $associationId, int $productId): int
    {
        $stmt = $this->connection->executeQuery(
            'SELECT id FROM pim_catalog_association WHERE owner_id=:owner_id AND association_type_id=:association_type_id',
            ['owner_id' => $productId, 'association_type_id' => $associationId]
        );
        $result = $stmt->fetch(\PDO::FETCH_COLUMN);
        if (!$result) {
            throw new \LogicException('Something went wrong');
        }

        return (int)$result;
    }

    private function saveProductAssociation(int $newAssociationId, int $ownerProductId): void
    {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product(association_id, product_id)
VALUES
(:association_id, :product_id)
ON DUPLICATE KEY UPDATE
    association_id = :association_id,
    product_id = :product_id
SQL;
        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_id' => $newAssociationId, 'product_id' => $ownerProductId]
        );
    }

    private function saveProductModelAssociation(int $newAssociationId, int $ownerProductId): void
    {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product_model_to_product(association_id, product_id)
VALUES
(:association_id, :product_id)
ON DUPLICATE KEY UPDATE
    association_id = :association_id,
    product_id = :product_id
SQL;
        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_id' => $newAssociationId, 'product_id' => $ownerProductId]
        );
    }
}
