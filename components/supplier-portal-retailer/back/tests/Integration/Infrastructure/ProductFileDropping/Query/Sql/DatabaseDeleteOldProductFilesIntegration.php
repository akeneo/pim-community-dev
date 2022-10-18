<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DeleteOldProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseDeleteOldProductFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDeletesOldProductFiles(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->build(),
        );

        $supplier = new Supplier(
            'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
            'supplier_code',
            'Supplier label',
        );

        for ($i = 0; 3 > $i; $i++) {
            $this->get(ProductFileRepository::class)->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplier)
                    ->withOriginalFilename(sprintf('file%d.xlsx', $i + 1))
                    ->withPath(sprintf('supplier-1/file%d.xlsx', $i + 1))
                    ->uploadedAt(
                        (new \DateTimeImmutable())->add(
                            \DateInterval::createFromDateString(sprintf('-%d days', ($i + 1) * 40)),
                        ),
                    )
                    ->build(),
            );
        }
        $this->get(DeleteOldProductFiles::class)();
        $supplierProductFilenames = $this->findSupplierProductFiles();

        static::assertEqualsCanonicalizing([
            ['original_filename' => 'file1.xlsx'],
            ['original_filename' => 'file2.xlsx'],
        ], $supplierProductFilenames);
    }

    private function findSupplierProductFiles(): array
    {
        $sql = <<<SQL
            SELECT original_filename
            FROM `akeneo_supplier_portal_supplier_product_file`
        SQL;

        return $this->get(Connection::class)
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }
}
