<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountFilteredProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseCountFilteredProductFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(CountFilteredProductFiles::class)());
    }

    /** @test */
    public function itCanCountFilteredProductFiles(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->build(),
        );

        $supplier = new Supplier(
            '44ce8069-8da1-4986-872f-311737f46f00',
            'supplier_code',
            'Supplier label',
        );

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file1.xlsx')
                ->uploadedBySupplier($supplier)
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file2.xlsx')
                ->uploadedBySupplier($supplier)
                ->build(),
        );

        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('file1'));
        static::assertSame(0, $this->get(CountFilteredProductFiles::class)('file3'));
    }
}
