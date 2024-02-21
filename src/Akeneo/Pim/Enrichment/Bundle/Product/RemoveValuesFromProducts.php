<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\ResilientDeadlockConnection;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\DeadlockException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveValuesFromProducts
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UnitOfWorkAndRepositoriesClearer $clearer,
        private readonly ResilientDeadlockConnection $resilientDeadlockConnection,
    ) {
    }

    /**
     * @param string[] $attributeCodes
     * @param string[] $productUuids
     */
    public function forAttributeCodes(array $attributeCodes, array $productUuids): void
    {
        $this->removeValuesForAttributeCodes($attributeCodes, $productUuids);
        $this->dispatchProductSaveEvents($productUuids);

        $this->clearer->clear();
    }

    /**
     * @param string[] $attributeCodes
     * @param string[] $productUuids
     *
     * @throws DeadlockException|Exception
     */
    private function removeValuesForAttributeCodes(array $attributeCodes, array $productUuids): void
    {
        $paths = implode(
            ',',
            array_map(fn ($attributeCode) => $this->connection->quote(sprintf('$."%s"', $attributeCode)), $attributeCodes)
        );

        $uuidsAsBytes = \array_map(fn ($productUuid) => Uuid::fromString($productUuid)->getBytes(), $productUuids);

        $this->resilientDeadlockConnection->executeQuery(
            <<<SQL
    UPDATE pim_catalog_product
    SET raw_values = JSON_REMOVE(raw_values, $paths)
    WHERE uuid IN (:uuids)
    SQL,
            [
                'uuids' => $uuidsAsBytes,
            ],
            [
                'uuids' => Connection::PARAM_STR_ARRAY,
            ],
            sprintf('%s:%s', self::class, 'removeValuesForAttributeCodes'),
        );
    }

    /**
     * @param string[] $productUuids
     */
    private function dispatchProductSaveEvents(array $productUuids): void
    {
        $products = $this->productRepository->getItemsFromUuids($productUuids);

        foreach ($products as $product) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($product, [
                    'unitary' => false,
                ]),
                StorageEvents::POST_SAVE
            );
        }

        $this->eventDispatcher->dispatch(
            new GenericEvent($products, [
                'unitary' => false,
            ]),
            StorageEvents::POST_SAVE_ALL
        );
    }
}
