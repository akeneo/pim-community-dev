<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetAllProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetAllProductFilesCountIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(GetAllProductFilesCount::class)());
    }

    /** @test */
    public function itReturnsTheTotalNumberOfProductFiles(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->build(),
        );

        for ($i = 1; 15 >= $i; $i++) {
            $this->get(ProductFileRepository::class)->save(
                (new ProductFileBuilder())
                    ->withUploadedBySupplier('44ce8069-8da1-4986-872f-311737f46f00')
                    ->build(),
            );
        }

        static::assertSame(15, $this->get(GetAllProductFilesCount::class)());
    }
}
