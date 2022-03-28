<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\Sql;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Identifier;
use Doctrine\DBAL\Connection;

final class DatabaseRepository implements Supplier\Repository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(Supplier\Model\Supplier $supplier): void
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

        $this->deleteContributors($supplier->identifier());
        $this->persistContributors($supplier);
    }

    public function getByIdentifier(Supplier\ValueObject\Identifier $identifier): ?Supplier\Model\Supplier
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

        return false !== $row ? Supplier\Model\Supplier::create(
            $row['identifier'],
            $row['code'],
            $row['label']
        ) : null;
    }

    public function delete(Identifier $identifier): void
    {
        $this->connection->delete(
            'akeneo_onboarder_serenity_supplier',
            ['identifier' => (string) $identifier]
        );
    }

    private function deleteContributors(string $supplierIdentifier): void
    {
        $this->connection->delete(
            'akeneo_onboarder_serenity_supplier_contributor',
            ['supplier_identifier' => (string) $supplierIdentifier]
        );
    }

    private function persistContributors(Supplier\Model\Supplier $supplier): void
    {
        $sql = <<<SQL
INSERT INTO `akeneo_onboarder_serenity_supplier_contributor` (email, supplier_identifier)
VALUES (:email, :supplierIdentifier)
SQL;
        $contributorEmails = $supplier->contributors()->toArray();

        foreach ($contributorEmails as $email) {
            $this->connection->executeQuery(
                $sql,
                [
                    'email' => $email,
                    'supplierIdentifier' => $supplier->identifier(),
                ]);
        }
    }
}
