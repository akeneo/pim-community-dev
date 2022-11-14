<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Doctrine\DBAL\Connection;

final class DatabaseRepository implements Repository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(Supplier $supplier): void
    {
        $sql = <<<SQL
            REPLACE INTO `akeneo_supplier_portal_supplier` (identifier, code, label, updated_at)
            VALUES (:identifier, :code, :label, :updatedAt)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'identifier' => $supplier->identifier(),
                'code' => $supplier->code(),
                'label' => $supplier->label(),
                'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
        );

        $this->deleteContributors($supplier->identifier());
        $this->persistContributors($supplier);
    }

    public function find(Identifier $identifier): ?Supplier
    {
        $sql = <<<SQL
            WITH contributor AS (
                SELECT supplier_identifier, JSON_ARRAYAGG(email) as contributor_emails
                FROM `akeneo_supplier_portal_supplier_contributor` contributor
                GROUP BY contributor.supplier_identifier
            )
            SELECT identifier, code, label, contributor.contributor_emails
            FROM `akeneo_supplier_portal_supplier` supplier
            LEFT JOIN contributor ON contributor.supplier_identifier = supplier.identifier
            WHERE identifier = :identifier
        SQL;

        $row = $this->connection->executeQuery(
            $sql,
            [
                'identifier' => (string) $identifier,
            ],
        )->fetchAssociative();

        return false !== $row ? Supplier::create(
            $row['identifier'],
            $row['code'],
            $row['label'],
            null !== $row['contributor_emails'] ? json_decode($row['contributor_emails'], true) : [],
        ) : null;
    }

    public function delete(Identifier $identifier): void
    {
        $this->connection->beginTransaction();

        $this->deleteContributors((string) $identifier);
        $this->connection->delete(
            'akeneo_supplier_portal_supplier',
            ['identifier' => (string) $identifier],
        );

        $this->connection->commit();
    }

    private function deleteContributors(string $supplierIdentifier): void
    {
        $this->connection->delete(
            'akeneo_supplier_portal_supplier_contributor',
            ['supplier_identifier' => $supplierIdentifier],
        );
    }

    private function persistContributors(Supplier $supplier): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_contributor` (email, supplier_identifier)
            VALUES (:email, :supplierIdentifier)
        SQL;

        $contributors = $supplier->contributors();

        foreach ($contributors as $contributor) {
            $this->connection->executeQuery(
                $sql,
                [
                    'email' => $contributor['email'],
                    'supplierIdentifier' => $supplier->identifier(),
                ],
            );
        }
    }
}
