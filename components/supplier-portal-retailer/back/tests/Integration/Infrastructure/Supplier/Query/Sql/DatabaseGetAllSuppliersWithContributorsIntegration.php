<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetAllSuppliersWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetAllSuppliersWithContributorsIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoSupplierToExport(): void
    {
        static::assertCount(0, $this->get(GetAllSuppliersWithContributors::class)());
    }

    /** @test */
    public function itGetsSupplierExport(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withContributors(['foo1@foo.bar', 'foo2@foo.bar'])
                ->build(),
        );

        $suppliers = $this->get(GetAllSuppliersWithContributors::class)();

        static::assertCount(1, $suppliers);
        static::assertSame('supplier_code', $suppliers[0]->code);
        static::assertSame('Supplier label', $suppliers[0]->label);
        static::assertSame(['foo1@foo.bar', 'foo2@foo.bar'], $suppliers[0]->contributors);
    }
}
