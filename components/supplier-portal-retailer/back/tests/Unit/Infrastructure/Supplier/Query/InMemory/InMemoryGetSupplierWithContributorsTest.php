<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use PHPUnit\Framework\TestCase;

final class InMemoryGetSupplierWithContributorsTest extends TestCase
{
    /** @test */
    public function itReturnsNullIfThereIsNoSupplier(): void
    {
        $sut = new InMemoryGetSupplierWithContributors(new InMemoryRepository());

        static::assertNull(($sut)('ca8baefd-0e05-4683-be48-6b9ff87e4cbc'));
    }

    /** @test */
    public function itGetsASupplierWithContributors(): void
    {
        $supplierRepository = new InMemoryRepository();
        $sut = new InMemoryGetSupplierWithContributors($supplierRepository);

        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('ca8baefd-0e05-4683-be48-6b9ff87e4cbc')
                ->withContributors(['foo@foo.bar', 'foo2@foo2.bar'])
                ->build(),
        );

        $supplier = ($sut)('ca8baefd-0e05-4683-be48-6b9ff87e4cbc');

        static::assertSame('ca8baefd-0e05-4683-be48-6b9ff87e4cbc', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier label', $supplier->label);
        static::assertCount(2, $supplier->contributors);
        static::assertSame('foo@foo.bar', $supplier->contributors[0]);
        static::assertSame('foo2@foo2.bar', $supplier->contributors[1]);
    }
}
