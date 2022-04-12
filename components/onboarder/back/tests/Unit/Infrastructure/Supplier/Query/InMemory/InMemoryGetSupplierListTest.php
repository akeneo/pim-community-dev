<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierList;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InMemoryGetSupplierListTest extends TestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoSupplier(): void
    {
        static::assertCount(0, (new InMemoryGetSupplierList(new InMemoryRepository()))());
    }

    /** @test */
    public function itGetsNoMoreThanFiftySuppliersAtATime(): void
    {
        $repository = new InMemoryRepository();
        $sut = new InMemoryGetSupplierList($repository);

        for ($i = 1; $i <= 60; $i++) {
            $repository->save(Write\Supplier\Model\Supplier::create(
                Uuid::uuid4()->toString(),
                sprintf('supplier_code_%d', $i),
                sprintf('Supplier %d label', $i),
                [],
            ));
        }

        static::assertCount(50, ($sut)());
    }

    /** @test */
    public function itSearchesOnSupplierLabel(): void
    {
        $repository = new InMemoryRepository();
        $sut = new InMemoryGetSupplierList($repository);

        $repository->save(Write\Supplier\Model\Supplier::create(
            Uuid::uuid4()->toString(),
            'walter_white',
            'Walter White',
            [],
        ));

        $supplierIdentifier = Uuid::uuid4()->toString();
        $repository->save(Write\Supplier\Model\Supplier::create(
            $supplierIdentifier,
            'jessie_pinkman',
            'Jessie Pinkman',
            [],
        ));

        static::assertSame($sut(1, 'pin')[$supplierIdentifier]->code, 'jessie_pinkman');
    }

    /** @test */
    public function itPaginatesTheSupplierList(): void
    {
        $repository = new InMemoryRepository();
        $sut = new InMemoryGetSupplierList($repository);

        for ($i = 1; $i <= 110; $i++) {
            $repository->save(Write\Supplier\Model\Supplier::create(
                Uuid::uuid4()->toString(),
                sprintf('supplier_code_%d', $i),
                sprintf('Supplier %d label', $i),
                [],
            ));
        }

        $suppliers = $sut(2);

        static::assertCount(50, $suppliers);
    }
}
