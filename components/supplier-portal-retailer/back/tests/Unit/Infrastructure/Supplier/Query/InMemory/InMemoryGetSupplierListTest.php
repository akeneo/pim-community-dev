<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierList;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
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

        for ($i = 1; 60 >= $i; $i++) {
            $repository->save(
                (new SupplierBuilder())
                    ->withIdentifier(Uuid::uuid4()->toString())
                    ->withCode(sprintf('supplier_code_%d', $i))
                    ->build(),
            );
        }

        static::assertCount(50, ($sut)());
    }

    /** @test */
    public function itSearchesOnSupplierLabel(): void
    {
        $repository = new InMemoryRepository();
        $sut = new InMemoryGetSupplierList($repository);

        $repository->save(
            (new SupplierBuilder())
                ->withCode('walter_white')
                ->withLabel('Walter White')
                ->build(),
        );

        $supplierIdentifier = Uuid::uuid4()->toString();
        $repository->save(
            (new SupplierBuilder())
                ->withIdentifier($supplierIdentifier)
                ->withCode('jessie_pinkman')
                ->withLabel('Jessie Pinkman')
                ->build(),
        );

        static::assertSame($sut(1, 'pin')[$supplierIdentifier]->code, 'jessie_pinkman');
    }

    /** @test */
    public function itPaginatesTheSupplierList(): void
    {
        $repository = new InMemoryRepository();
        $sut = new InMemoryGetSupplierList($repository);

        for ($i = 1; 110 >= $i; $i++) {
            $repository->save(
                (new SupplierBuilder())
                    ->withIdentifier(Uuid::uuid4()->toString())
                    ->withCode(sprintf('supplier_code_%d', $i))
                    ->withLabel(sprintf('Supplier %d label', $i))
                    ->build(),
            );
        }

        $suppliers = $sut(2);

        static::assertCount(50, $suppliers);
    }
}
