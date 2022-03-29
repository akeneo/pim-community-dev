<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Contributor\Repository\Sql;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor;
use Doctrine\DBAL\Connection;

final class DatabaseRepository implements Contributor\Repository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(Contributor\Model\Contributor $contributor): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_onboarder_serenity_supplier_contributor` (identifier, email, supplier_identifier)
            VALUES (:identifier, :email, :supplierIdentifier)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'identifier' => $contributor->identifier(),
                'email' => $contributor->email(),
                'supplierIdentifier' => $contributor->supplierIdentifier(),
            ]
        );
    }
}
