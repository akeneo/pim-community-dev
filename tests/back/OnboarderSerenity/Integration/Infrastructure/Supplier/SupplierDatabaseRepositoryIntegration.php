<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Integration\Infrastructure\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Identifier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\SupplierRepository;
use Akeneo\OnboarderSerenity\Integration\SqlIntegrationTestCase;

final class SupplierDatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itCreatesAndFindsASupplier(): void
    {
        $supplierRepository = $this->get(SupplierRepository::class);

        $supplierRepository->save(Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code'
        ));

        $supplier = $supplierRepository->find(Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'));

        static::assertInstanceOf(Supplier::class, $supplier);
    }

    /** @test */
    public function itUpdatesAnExistingSupplier(): void
    {
        $supplierRepository = $this->get(SupplierRepository::class);

        $supplierRepository->save(Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code'
        ));

        $supplier = $supplierRepository->find(Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'));

        static::assertInstanceOf(Supplier::class, $supplier);
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier code', $supplier->label());

        $supplierRepository->save(Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'new_supplier_code',
            'New supplier code'
        ));

        $supplier = $supplierRepository->find(Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'));

        static::assertInstanceOf(Supplier::class, $supplier);
        static::assertSame('new_supplier_code', $supplier->code());
        static::assertSame('New supplier code', $supplier->label());
    }
}
