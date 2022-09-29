<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemoryGetAllSuppliersWithContributors;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use PHPUnit\Framework\TestCase;

final class InMemoryGetAllSuppliersWithContributorsTest extends TestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoSuppliers(): void
    {
        $sut = new InMemoryGetAllSuppliersWithContributors(new InMemoryRepository());

        static::assertCount(0, $sut());
    }

    /** @test */
    public function itGetAllSuppliersWithContributors(): void
    {
        $supplierRepository = new InMemoryRepository();
        $sut = new InMemoryGetAllSuppliersWithContributors($supplierRepository);

        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('ca8baefd-0e05-4683-be48-6b9ff87e4cbc')
                ->withCode('supplier1')
                ->withLabel('Supplier1')
                ->withContributors(['foo1@foo.bar', 'foo2@foo.bar'])
                ->build(),
        );

        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1')
                ->withCode('supplier2')
                ->withLabel('Supplier2')
                ->build(),
        );

        $suppliers = ($sut)();

        static::assertCount(2, $suppliers);
        static::assertSame('ca8baefd-0e05-4683-be48-6b9ff87e4cbc', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->identifier);
        static::assertSame('supplier1', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->code);
        static::assertSame('Supplier1', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->label);
        static::assertCount(2, $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->contributors);
        static::assertSame('foo1@foo.bar', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->contributors[0]['email']);
        static::assertSame('foo2@foo.bar', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->contributors[1]['email']);
        static::assertSame('c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1', $suppliers['c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1']->identifier);
        static::assertSame('supplier2', $suppliers['c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1']->code);
        static::assertSame('Supplier2', $suppliers['c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1']->label);
        static::assertCount(0, $suppliers['c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1']->contributors);
    }
}
