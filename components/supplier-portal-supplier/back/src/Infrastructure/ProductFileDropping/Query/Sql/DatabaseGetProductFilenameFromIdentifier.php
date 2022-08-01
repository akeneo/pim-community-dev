<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilenameFromIdentifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilenameFromIdentifier implements GetProductFilenameFromIdentifier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(Identifier $identifier): ?Filename
    {
        $sql = <<<SQL
            SELECT original_filename
            FROM akeneo_supplier_portal_supplier_file
            WHERE identifier = :identifier;
        SQL;

        $supplierFilename = $this->connection->executeQuery(
            $sql,
            ['identifier' => (string) $identifier],
        )->fetchOne();

        if (false === $supplierFilename) {
            return null;
        }

        return Filename::fromString($supplierFilename);
    }
}
