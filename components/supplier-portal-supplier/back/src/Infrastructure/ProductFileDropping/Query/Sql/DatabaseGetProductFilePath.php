<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePath;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePath implements GetProductFilePath
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(Identifier $productFileIdentifier): ?Path
    {
        $sql = <<<SQL
            SELECT path 
            FROM akeneo_supplier_portal_supplier_file supplier_file 
            WHERE identifier = :identifier
        SQL;

        $path = $this->connection->executeQuery(
            $sql,
            [
                'identifier' => (string) $productFileIdentifier,
            ],
        )->fetchOne();

        if (false === $path) {
            return null;
        }

        return Path::fromString($path);
    }
}
