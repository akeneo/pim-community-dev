<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\GetAllSupplierCodes;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetAllSupplierCodesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsNothingIfThereIsNoSupplier(): void
    {
        static::assertEmpty($this->get(GetAllSupplierCodes::class)());
    }

    /** @test */
    public function itGetsAllTheSupplierCodes(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withCode('supplier_1')
                ->build(),
        );

        $supplierRepository->save(
            (new SupplierBuilder())
                ->withCode('supplier_2')
                ->build(),
        );

        static::assertEqualsCanonicalizing(['supplier_1', 'supplier_2'], $this->get(GetAllSupplierCodes::class)());
    }
}
