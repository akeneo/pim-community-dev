<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\SupplierExists;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Doctrine\DBAL\Connection;

final class DatabaseSupplierExists implements SupplierExists
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromCode(Supplier\ValueObject\Code $supplierCode): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM `akeneo_onboarder_serenity_supplier`
            WHERE code = :code
        SQL;

        return 1 === $this->connection->executeQuery(
            $sql,
            [
                'code' => (string) $supplierCode,
            ]
        )->rowCount();
    }
}
