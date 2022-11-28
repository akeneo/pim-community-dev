<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetSupplierProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetSupplierProductFilesCountIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
                    ->withOriginalFilename('file'.$i)
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
    }

    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(GetSupplierProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f01'));
    }

    /** @test */
    public function itReturnsTheNumberOfProductFilesForASupplierWithoutSearch(): void
    {
        static::assertSame(15, $this->get(GetSupplierProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itReturnsTheNumberOfProductFilesForASupplierWithSearch(): void
    {
        static::assertSame(7, $this->get(GetSupplierProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00', '1'));
    }
}
