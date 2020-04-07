<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveBidirectionnalAssociations implements EventSubscriberInterface
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
            StorageEvents::POST_SAVE => 'saveOneBidirectionalAssociation'
            // Manage bulk post save ? (post_save_all event or unitary with bulk option?)
        ];
    }

    public function saveOneBidirectionalAssociation(GenericEvent $event)
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->execute($product);
    }

    public function execute(ProductInterface $product): void
    {
        foreach ($product->getAssociations() as $association) {
            $this->connection->transactional(
                function () use ($association, $product) {
                    if ($association->getAssociationType()->isBidirectional()) {
                        $this->saveBidirectionalProductAssociations($association, $product);
                        // TODO: $this->saveBidirectionalProductModelAssociations($association, $product);
                        // TODO: $this->saveBidirectionalProductModelToProductAssociations($association, $product);
                        // TODO: $this->saveBidirectionalProductToProductModelAssociations($association, $product);}
                    }
                }
            );
        }
    }

    private function saveBidirectionalProductAssociations(
        AssociationInterface $association,
        ProductInterface $product
    ): void {
        $this->removeInvertedProductAssociationsDeleted($association, $product);
        $this->saveBidirectionalProductAssociation($association, $product->getId());
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
);
SQL;

        if (!$ownerProductIds->isEmpty()) {
            $query = <<<SQL
DELETE FROM pim_catalog_association_product
WHERE association_id IN (
	SELECT * FROM (
		SELECT a.id
	    FROM pim_catalog_association a INNER JOIN pim_catalog_association_product ap ON ap.association_id = a.id
	    WHERE association_type_id = :association_type_id AND product_id = :product_id AND owner_id NOT IN (:ownerProductIdsFormatted)
	) as association_id_to_delete
);
SQL;
            $params['ownerProductIdsFormatted'] = $ownerProductIds->toArray();
            $types['ownerProductIdsFormatted'] = Connection::PARAM_INT_ARRAY;
        }

        $this->connection->executeUpdate($query, $params, $types);
    }

    private function saveBidirectionalProductAssociation(
        AssociationInterface $productAssociation,
        $ownerProductId
    ): void {
        $productIds = $productAssociation->getProducts()->map(
            function (ProductInterface $product) {
                return $product->getId();
            }
        );
        if ($productIds->isEmpty()) {
            return;
        }

        foreach ($productIds as $productId) {
            $this->saveNewAssociation($productAssociation, $productId);
            $newAssociationId = $this->fetchAssociationId(
                $productAssociation->getAssociationType()->getId(),
                $productId
            );
            $this->saveProductAssociation($newAssociationId, $ownerProductId);
        }
    }

    private function saveNewAssociation(AssociationInterface $productAssociation, $productId)
    {
        $insertAssociation = <<<SQL
INSERT INTO pim_catalog_association (association_type_id, owner_id)
VALUES 
(:association_type_id, :product_id)
ON DUPLICATE KEY UPDATE
    association_type_id = :association_type_id,
    owner_id = :product_id
;
SQL;
        $this->connection->executeUpdate(
            $insertAssociation,
            [
                'association_type_id' => $productAssociation->getAssociationType()->getId(),
                'product_id'          => $productId
            ]
        );
    }

    private function fetchAssociationId(int $associationId, $productId): int
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

    private function saveProductAssociation($newAssociationId, $ownerProductId)
    {
        $insertProductAssociation = <<<SQL
INSERT INTO pim_catalog_association_product (association_id, product_id)
VALUES 
(:association_id, :product_id)
ON DUPLICATE KEY UPDATE
    association_id = :association_id,
    product_id = :product_id
;
SQL;
        $this->connection->executeUpdate(
            $insertProductAssociation,
            ['association_id' => $newAssociationId, 'product_id' => $ownerProductId]
        );
    }
}
