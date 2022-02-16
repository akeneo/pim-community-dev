<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddUuidSubscriber implements EventSubscriberInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'fillSingleUuid',
            StorageEvents::POST_SAVE_ALL => 'fillMultipleUuid',
        ];
    }

    public function fillSingleUuid(GenericEvent $event): void
    {
        $product = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;
        if (false === $unitary || !$product instanceof ProductInterface) {
            return;
        }
        $this->fillUuids([$product->getIdentifier()]);
    }

    public function fillMultipleUuid(GenericEvent $event): void
    {
        $products = $event->getSubject();
        if (!reset($products) instanceof ProductInterface) {
            return;
        }

        $identifiers = [];
        foreach ($products as $product) {
            $identifiers[] = $product->getIdentifier();
        }
        $this->fillUuids($identifiers);
    }

    private function fillUuids(array $identifiers): void
    {
        if (!$this->columnExists('pim_catalog_product', 'uuid')) {
            return;
        }

        foreach ($identifiers as $identifier) {
            $this->connection->executeQuery(
                'UPDATE pim_catalog_product SET uuid=UUID_TO_BIN(:uuid) WHERE identifier=:identifier',
                [
                    'uuid' => Uuid::uuid4()->toString(),
                    'identifier' => $identifier
                ]
            );
        }
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            sprintf('SHOW COLUMNS FROM %s LIKE :columnName', $tableName),
            [
                'columnName' => $columnName,
            ]
        );

        return count($rows) >= 1;
    }
}
