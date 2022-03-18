<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplier implements GetSupplier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(Supplier\ValueObject\Code $supplierCode): ?Supplier\Model\Supplier
    {
        $supplier = $this->connection->executeQuery(
            <<<SQL
                SELECT identifier, code, label
                FROM `akeneo_onboarder_serenity_supplier`
                WHERE code = :supplierCode
            SQL
            ,
            [
                'supplierCode' => $supplierCode
            ]
        )->fetchAssociative();

        return false !== $supplier ? Supplier\Model\Supplier::create(
            $supplier['identifier'],
            $supplier['code'],
            $supplier['label']
        ) : null;
    }
}
