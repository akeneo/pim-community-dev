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

    public function forAttributeCodes(array $attributeCodes, array $productIdentifiers): void
    {
        $this->removeValuesForAttributeCodes($attributeCodes, $productIdentifiers);
        $this->dispatchProductSaveEvents($productIdentifiers);

        $this->clearer->clear();
    }

    /**
     * @throws DeadlockException|Exception
     */
    private function removeValuesForAttributeCodes(array $attributeCodes, array $productIdentifiers): void
    {
        $paths = implode(
            ',',
            array_map(fn ($attributeCode) => $this->connection->quote(sprintf('$."%s"', $attributeCode)), $attributeCodes)
        );

        $this->resilientDeadlockConnection->executeQuery(
            <<<SQL
    UPDATE pim_catalog_product
    SET raw_values = JSON_REMOVE(raw_values, $paths)
    WHERE identifier IN (:identifiers)
    SQL,
            [
                'identifiers' => $productIdentifiers,
            ],
            [
                'identifiers' => Connection::PARAM_STR_ARRAY,
            ],
            sprintf('%s:%s', self::class, 'removeValuesForAttributeCodes'),
        );
    }

    private function dispatchProductSaveEvents(array $productIdentifiers): void
    {
        $products = $this->productRepository->findBy(['identifier' => $productIdentifiers]);

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
