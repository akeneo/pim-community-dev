<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierList;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributorCount;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

final class DatabaseGetSupplierListIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoSupplier(): void
    {
        static::assertCount(0, ($this->get(GetSupplierList::class))());
    }

    /** @test */
    public function itGetsNoMoreThanFiftySuppliersAtATime(): void
    {
        $supplierRepository = $this->get(Repository::class);
        for ($i = 1; 60 >= $i; $i++) {
            $supplierRepository->save(
                (new SupplierBuilder())
                    ->withIdentifier(Uuid::uuid4()->toString())
                    ->withCode(sprintf('supplier_code_%d', $i))
                    ->withLabel(sprintf('Supplier %d label', $i))
                    ->build(),
            );
        }

        static::assertCount(50, $this->get(GetSupplierList::class)());
    }

    /** @test */
    public function itSearchesOnSupplierLabel(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withCode('walter_white')
                ->withLabel('Walter White')
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withCode('jessie_pinkman')
                ->withLabel('Jessie Pinkman')
                ->build(),
        );

        static::assertSame($this->get(GetSupplierList::class)(1, 'Pin')[0]->code, 'jessie_pinkman');
    }

    /** @test */
    public function itPaginatesTheSupplierList(): void
    {
        $supplierRepository = $this->get(Repository::class);
        for ($i = 1; 110 >= $i; $i++) {
            $supplierRepository->save(
                (new SupplierBuilder())
                    ->withCode(sprintf('supplier_code_%d', $i))
                    ->withLabel(sprintf('Supplier %d label', $i))
                    ->build(),
            );
        }

        $suppliers = $this->get(GetSupplierList::class)(3);

        static::assertCount(10, $suppliers);
    }

    /** @test */
    public function itSortsTheSupplierListInAnAscendingDirection(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withCode('supplier_code_a')
                ->withLabel('Supplier code a')
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withCode('supplier_code_b')
                ->withLabel('Supplier code b')
                ->build(),
        );

        $suppliers = $this->get(GetSupplierList::class)();

        static::assertSame('supplier_code_a', $suppliers[0]->code);
        static::assertSame('supplier_code_b', $suppliers[1]->code);
    }

    /** @test */
    public function itReturnsAContributorCountBySupplier(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_1')
                ->withLabel('Supplier 1')
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->withCode('supplier_2')
                ->withLabel('Supplier 2')
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );

        $suppliers = $this->get(GetSupplierList::class)();

        static::assertEquals(
            new SupplierWithContributorCount(
                '44ce8069-8da1-4986-872f-311737f46f00',
                'supplier_1',
                'Supplier 1',
                0,
            ),
            $suppliers[0],
        );
        static::assertEquals(
            new SupplierWithContributorCount(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'supplier_2',
                'Supplier 2',
                2,
            ),
            $suppliers[1],
        );
    }
}
