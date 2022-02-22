<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Identifier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\SupplierRepository;
use Doctrine\DBAL\Connection;

final class SupplierDatabaseRepository implements SupplierRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function add(Supplier $supplier): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_onboarder_serenity_supplier` (identifier, code, label)
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

    public function find(Identifier $identifier): ?Supplier
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

        return false !== $row ? Supplier::create(
            $row['identifier'],
            $row['code'],
            $row['label']
        ) : null;
    }
}
