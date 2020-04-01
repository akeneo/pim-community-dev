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
            if ($association->getAssociationType()->isBidirectional()) {
                $this->saveBidirectionalLinks($association, $product);
            }
        }
    }

    private function saveBidirectionalLinks(AssociationInterface $association, ProductInterface $product): void
    {
        $productIds = $association->getProducts()->map(function(ProductInterface $product) {
            return $product->getId();
        });
        $this->saveBidirectionalProductAssociation($association, $productIds, $product->getId());
//        $this->saveProductModelBidirectionalLinks($association, $productIds);
    }

    /**
     * @param AssociationInterface $productAssociation
     * @param                      $productIds
     *
     * @throws \Throwable
     */
    private function saveBidirectionalProductAssociation(AssociationInterface $productAssociation, $productIds, $ownerProductId): void
    {
        if ($productIds->isEmpty()) {
            return;
        }

        foreach ($productIds as $productId) {
            $this->saveNewAssociation($productAssociation, $productId);
            $newAssociationId = $this->fetchAssociationId($productAssociation->getAssociationType()->getId(), $productId);
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

        return (int) $result;
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
        $this->connection->executeUpdate($insertProductAssociation, ['association_id' => $newAssociationId, 'product_id' => $ownerProductId]);
    }
}
