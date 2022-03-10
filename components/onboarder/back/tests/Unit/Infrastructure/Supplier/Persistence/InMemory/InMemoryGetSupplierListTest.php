<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Persistence\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Persistence\InMemory\InMemoryGetSupplierList;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InMemoryGetSupplierListTest extends TestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoSupplier(): void
    {
        static::assertCount(0, (new InMemoryGetSupplierList())());
    }

    /** @test */
    public function itGetsNoMoreThanFiftySuppliersAtATime(): void
    {
        $sut = new InMemoryGetSupplierList();

        for ($i = 1; $i <= 60; $i++) {
            $sut->save(Write\Supplier\Model\Supplier::create(
                Uuid::uuid4()->toString(),
                sprintf('supplier_code_%d', $i),
                sprintf('Supplier %d label', $i)
            ));
        }

        static::assertCount(50, ($sut)());
    }

    /** @test */
    public function itSearchesOnSupplierLabel(): void
    {
        $sut = new InMemoryGetSupplierList();

        $sut->save(Write\Supplier\Model\Supplier::create(
            Uuid::uuid4()->toString(),
            'walter_white',
            'Walter White'
        ));

        $supplierIdentifier = Uuid::uuid4()->toString();
        $sut->save(Write\Supplier\Model\Supplier::create(
            $supplierIdentifier,
            'jessie_pinkman',
            'Jessie Pinkman'
        ));

        static::assertSame($sut(1, 'Pin')[$supplierIdentifier]->code(), 'jessie_pinkman');
    }

    /** @test */
    public function itPaginatesTheSupplierList(): void
    {
        $sut = new InMemoryGetSupplierList();

        for ($i = 1; $i <= 110; $i++) {
            $sut->save(Write\Supplier\Model\Supplier::create(
                Uuid::uuid4()->toString(),
                sprintf('supplier_code_%d', $i),
                sprintf('Supplier %d label', $i)
            ));
        }

        $suppliers = $sut(2);

        static::assertCount(50, $suppliers);
    }
}
