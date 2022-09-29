<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\Supplier\Repository\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use PHPUnit\Framework\TestCase;

final class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itCreatesAndFindsASupplier(): void
    {
        $supplierRepository = new InMemoryRepository();

        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->build(),
        );

        $supplier = $supplierRepository->find(
            Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02',
            ),
        );

        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier label', $supplier->label());
    }

    /** @test */
    public function itCreatesAndFindsASupplierByItsCode(): void
    {
        $supplierRepository = new InMemoryRepository();

        $supplierRepository->save((new SupplierBuilder())->build());

        $supplier = $supplierRepository->findByCode('supplier_code');

        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier label', $supplier->label());
    }

    /** @test */
    public function itUpdatesASupplier(): void
    {
        $supplierRepository = new InMemoryRepository();

        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->withCode('new_supplier_code')
                ->withLabel('New supplier label')
                ->build(),
        );

        $supplier = $supplierRepository->find(
            Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02',
            ),
        );

        static::assertSame('new_supplier_code', $supplier->code());
        static::assertSame('New supplier label', $supplier->label());
    }

    /** @test */
    public function itReturnsNullWhenASupplierCannotBeFound(): void
    {
        static::assertNull(
            (new InMemoryRepository())
                ->find(Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')),
        );
    }

    /** @test */
    public function itDeletesASupplier(): void
    {
        $supplierRepository = new InMemoryRepository();
        $identifier = Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier((string) $identifier)
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f01')
                ->withCode('supplier_code2')
                ->withLabel('Supplier code2')
                ->build(),
        );
        $supplierRepository->delete($identifier);

        $this->assertNull($supplierRepository->find($identifier));
        $this->assertInstanceOf(
            Supplier::class,
            $supplierRepository->find(Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f01')),
        );
    }
}
