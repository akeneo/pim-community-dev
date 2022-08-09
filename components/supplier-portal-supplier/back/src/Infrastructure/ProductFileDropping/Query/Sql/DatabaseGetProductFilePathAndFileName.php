<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePathAndFileName implements GetProductFilePathAndFileName
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(Identifier $productFileIdentifier): ?ProductFilePathAndFileName
    {
        $sql = <<<SQL
            SELECT path, original_filename
            FROM akeneo_supplier_portal_supplier_file
            WHERE identifier = :identifier;
        SQL;

        $productFile = $this->connection->executeQuery(
            $sql,
            ['identifier' => (string) $productFileIdentifier],
        )->fetchAssociative();

        if (false === $productFile) {
            return null;
        }

        return new ProductFilePathAndFileName($productFile['original_filename'], $productFile['path']);
    }
}
