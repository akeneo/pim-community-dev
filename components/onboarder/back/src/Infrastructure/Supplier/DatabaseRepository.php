<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier;
use Doctrine\DBAL\Connection;

final class DatabaseRepository implements Supplier\Repository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(Supplier\Supplier $supplier): void
    {
        $sql = <<<SQL
            REPLACE INTO `akeneo_onboarder_serenity_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'identifier' => $supplier->identifier(),
                'code' => $supplier->code(),
                'label' => $supplier->label(),
            ]
        );
    }

    public function find(Supplier\Identifier $identifier): ?Supplier\Supplier
    {
        $sql = <<<SQL
            SELECT identifier, code, label
            FROM `akeneo_onboarder_serenity_supplier`
            WHERE identifier = :identifier
        SQL;

        $row = $this->connection->executeQuery(
            $sql,
            [
                'identifier' => (string) $identifier,
            ]
        )->fetchAssociative();

        return false !== $row ? Supplier\Supplier::create(
            $row['identifier'],
            $row['code'],
            $row['label']
        ) : null;
    }
}
