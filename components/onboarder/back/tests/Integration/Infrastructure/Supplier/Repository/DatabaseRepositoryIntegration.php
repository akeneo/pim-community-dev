<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Repository;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itCreatesAndFindsASupplier(): void
    {
        $supplierRepository = $this->get(Supplier\Repository::class);

        $supplierRepository->save(Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code'
        ));

        $supplierRepository->save(Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f03',
            'other_supplier_code',
            'Other supplier code'
        ));

        $supplier = $supplierRepository->find(
            Supplier\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02'
            )
        );

        static::assertInstanceOf(Supplier\Model\Supplier::class, $supplier);
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier code', $supplier->label());
    }

    /** @test */
    public function itUpdatesAnExistingSupplier(): void
    {
        $supplierRepository = $this->get(Supplier\Repository::class);

        $supplierRepository->save(Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code'
        ));

        $supplierRepository->save(Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'new_supplier_code',
            'New supplier code'
        ));

        $supplier = $supplierRepository->find(
            Supplier\ValueObject\Identifier::fromString(
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
            $this->get(Supplier\Repository::class)->find(
                Supplier\ValueObject\Identifier::fromString(
                    '44ce8069-8da1-4986-872f-311737f46f02'
                )
            )
        );
    }

    /** @test */
    public function itDeletesASupplier(): void
    {
        $supplierRepository = $this->get(Supplier\Repository::class);
        $supplierRepository->save(Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code'
        ));
        $supplierRepository->save(Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f01',
            'supplier_code2',
            'Supplier code2'
        ));
        $this->get(Supplier\Repository::class)->delete(
            Supplier\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02'
            )
        );
        static::assertNull(
            $this->get(Supplier\Repository::class)->find(
                Supplier\ValueObject\Identifier::fromString(
                    '44ce8069-8da1-4986-872f-311737f46f02'
                )
            )
        );

        static::assertInstanceOf(Supplier\Model\Supplier::class, $supplierRepository->find(
            Supplier\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f01'
            )
        ));
    }
}
