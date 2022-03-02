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

        $rows = \array_map(
            static fn (string $identifier): string => \sprintf("ROW(?, '%s')", Uuid::uuid4()->toString()),
            $identifiers
        );

        $sql = <<<SQL
        WITH
        new_product_uuid AS (
            SELECT * FROM (VALUES
                {rows}
            ) as t(identifier, uuid)
        )
        UPDATE pim_catalog_product p, new_product_uuid
        SET p.uuid = UUID_TO_BIN(new_product_uuid.uuid)
        WHERE p.identifier = new_product_uuid.identifier AND p.uuid IS NULL;
        SQL;

        $sql = \strtr($sql, [
            '{rows}' => \implode(',', $rows),
        ]);

        $this->connection->executeQuery($sql, $identifiers);
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
