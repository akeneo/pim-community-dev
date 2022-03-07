<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itCreatesASupplierAndReturnsIt(): void
    {
        $supplierRepository = new InMemoryRepository();

        $supplierRepository->save(
            Supplier\Supplier::create(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'supplier_code',
                'Supplier code'
            )
        );

        $supplier = $supplierRepository->find(
            Supplier\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02'
            )
        );

        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier code', $supplier->label());
    }

    /** @test */
    public function itUpdatesASupplier(): void
    {
        $supplierRepository = new InMemoryRepository();

        $supplierRepository->save(
            Supplier\Supplier::create(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'new_supplier_code',
                'New supplier code'
            )
        );

        $supplier = $supplierRepository->find(
            Supplier\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02'
            )
        );

        static::assertSame('new_supplier_code', $supplier->code());
        static::assertSame('New supplier code', $supplier->label());
    }

    /** @test */
    public function itReturnsNullWhenASupplierCannotBeFound(): void
    {
        static::assertNull(
            (new InMemoryRepository())
                ->find(Supplier\Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'))
        );
    }
}
