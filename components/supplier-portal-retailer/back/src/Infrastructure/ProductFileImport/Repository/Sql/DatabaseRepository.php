<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Doctrine\DBAL\Connection;

final class DatabaseRepository implements ProductFileRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(ProductFile $productFile): void
    {
        // TODO: Implement save() method.
    }

    public function find(Identifier $identifier): ?ProductFile
    {
        // TODO: Implement find() method.
    }
}
