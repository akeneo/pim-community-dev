<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveValuesFromProductModels
{
    private ProductModelRepositoryInterface $productModelRepository;
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private UnitOfWorkAndRepositoriesClearer $clearer;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        UnitOfWorkAndRepositoriesClearer $clearer
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->clearer = $clearer;
    }

    public function forAttributeCodes(array $attributeCodes, array $productModelIdentifiers): void
    {
        $paths = implode(
            ',',
            array_map(fn ($attributeCode) => $this->connection->quote(sprintf('$."%s"', $attributeCode)), $attributeCodes)
        );

        $this->connection->executeQuery(
            <<<SQL
UPDATE pim_catalog_product_model
SET raw_values = JSON_REMOVE(raw_values, $paths)
WHERE code IN (:identifiers)
SQL,
            [
                'identifiers' => $productModelIdentifiers,
            ],
            [
                'identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $productModels = $this->productModelRepository->findBy(['code' => $productModelIdentifiers]);
        $this->eventDispatcher->dispatch(
            StorageEvents::POST_SAVE_ALL,
            new GenericEvent($productModels, [
                'unitary' => false,
            ])
        );
        $this->clearer->clear();
    }
}
