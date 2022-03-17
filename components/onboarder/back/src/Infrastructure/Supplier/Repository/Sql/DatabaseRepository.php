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
    }

    public function delete(Identifier $identifier): void
    {
        $this->connection->delete(
            'akeneo_onboarder_serenity_supplier',
            ['identifier' => (string) $identifier]
        );
    }
}
