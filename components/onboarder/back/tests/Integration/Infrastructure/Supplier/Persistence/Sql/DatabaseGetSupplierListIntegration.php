<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Persistence\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierList;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
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
        $supplierRepository = $this->get(Supplier\Repository::class);

        for ($i = 1; $i <= 60; $i++) {
            $supplierRepository->save(Supplier\Supplier::create(
                (string)Uuid::uuid4(),
                sprintf('supplier_code_%d', $i),
                sprintf('Supplier %d label', $i)
            ));
        }

        static::assertCount(50, $this->get(GetSupplierList::class)());
    }

    /** @test */
    public function itSearchesOnSupplierLabel(): void
    {
        $supplierRepository = $this->get(Supplier\Repository::class);

        $supplierRepository->save(Supplier\Supplier::create(
            (string)Uuid::uuid4(),
            'walter_white',
            'Walter White'
        ));

        $supplierRepository->save(Supplier\Supplier::create(
            (string)Uuid::uuid4(),
            'jessie_pinkman',
            'Jessie Pinkman'
        ));

        static::assertSame($this->get(GetSupplierList::class)(1, 'Pin')[0]->code(), 'jessie_pinkman');
    }

    /** @test */
    public function itPaginatesTheSupplierList(): void
    {
        $supplierRepository = $this->get(Supplier\Repository::class);

        for ($i = 1; $i <= 110; $i++) {
            $supplierRepository->save(Supplier\Supplier::create(
                (string)Uuid::uuid4(),
                sprintf('supplier_code_%d', $i),
                sprintf('Supplier %d label', $i)
            ));
        }

        $suppliers = $this->get(GetSupplierList::class)(2);

        static::assertCount(50, $suppliers);
    }

    /** @test */
    public function itSortsTheSupplierListInAnUpwardDirection(): void
    {
        $supplierRepository = $this->get(Supplier\Repository::class);

        $supplierRepository->save(Supplier\Supplier::create(
            (string)Uuid::uuid4(),
            'supplier_code_b',
            'Supplier B label',
        ));

        $supplierRepository->save(Supplier\Supplier::create(
            (string)Uuid::uuid4(),
            'supplier_code_a',
            'Supplier A label',
        ));

        $suppliers = $this->get(GetSupplierList::class)();

        static::assertSame('supplier_code_a', $suppliers[0]->code());
        static::assertSame('supplier_code_b', $suppliers[1]->code());
    }
}
