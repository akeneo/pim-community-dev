<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\Event\SavedProductIdentifier;
use Akeneo\Pim\Enrichment\Component\Product\Model\Event\SavedProductIdentifierCollection;
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
        $this->removeValuesForAttributeCodes($attributeCodes, $productIdentifiers);
        $this->dispatchProductSaveEvents($productIdentifiers);

        $this->clearer->clear();
    }

    private function removeValuesForAttributeCodes(array $attributeCodes, array $productIdentifiers): void
    {
        $paths = implode(
            ',',
            array_map(fn ($attributeCode) => $this->connection->quote(sprintf('$."%s"', $attributeCode)), $attributeCodes)
        );

        $this->connection->executeQuery(
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
            ]
        );
    }

    private function dispatchProductSaveEvents(array $productIdentifiers): void
    {
        foreach ($productIdentifiers as $productIdentifier) {
            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE,
                new GenericEvent(SavedProductIdentifier::fromIdentifier($productIdentifier), [
                    'unitary' => false,
                ])
            );
        }

        $this->eventDispatcher->dispatch(
            StorageEvents::POST_SAVE_ALL,
            new GenericEvent(SavedProductIdentifierCollection::fromIdentifiers($productIdentifiers), [
                'unitary' => false,
            ])
        );
    }
}
