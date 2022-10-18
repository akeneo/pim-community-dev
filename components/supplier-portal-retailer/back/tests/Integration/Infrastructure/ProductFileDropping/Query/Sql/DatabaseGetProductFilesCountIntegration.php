<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetProductFilesCountIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(GetProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itReturnsTheTotalNumberOfProductFiles(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_1')
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('a20576cd-840f-4124-9900-14d581491387')
                ->withCode('supplier_2')
                ->build(),
        );

        $productFileRepository = $this->get(ProductFileRepository::class);

        $supplierOne = new Supplier(
            '44ce8069-8da1-4986-872f-311737f46f00',
            'supplier_1',
            'Supplier 1 label',
        );
        for ($i = 1; 15 >= $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplierOne)
                    ->build(),
            );
        }

        $supplierTwo = new Supplier(
            'a20576cd-840f-4124-9900-14d581491387',
            'supplier_2',
            'Supplier 2 label',
        );
        for ($i = 1; 10 >= $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplierTwo)
                    ->build(),
            );
        }

        static::assertSame(15, $this->get(GetProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }
}
