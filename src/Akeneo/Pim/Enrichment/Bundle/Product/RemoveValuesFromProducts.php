<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveValuesFromProducts
{
    private ProductRepositoryInterface $productRepository;
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private UnitOfWorkAndRepositoriesClearer $clearer;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        UnitOfWorkAndRepositoriesClearer $clearer
    ) {
        $this->productRepository = $productRepository;
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->clearer = $clearer;
    }

    public function forAttributeCodes(array $attributeCodes, array $productIdentifiers): void
    {
        $this->connection->executeQuery(
            <<<SQL
    UPDATE pim_catalog_product
    SET raw_values = JSON_REMOVE(raw_values, :json_path)
    WHERE identifier IN (:identifiers)
    SQL,
            [
                'json_path' => implode(', ', array_map(fn ($attributeCode) => sprintf('$.%s', $attributeCode), $attributeCodes)),
                'identifiers' => $productIdentifiers,
            ],
            [
                'json_path' => \PDO::PARAM_STR,
                'identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $products = $this->productRepository->findBy(['identifier' => $productIdentifiers]);
        $this->eventDispatcher->dispatch(
            StorageEvents::POST_SAVE_ALL,
            new GenericEvent($products, [
                'unitary' => false,
            ])
        );
        $this->clearer->clear();
    }
}
