<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Repository\InMemory;

use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itCreatesAndFindsASupplier(): void
    {
        $supplierRepository = new InMemoryRepository();

        $supplierRepository->save(
            \Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier::create(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'supplier_code',
                'Supplier code',
                [],
            ),
        );

        $supplier = $supplierRepository->find(
            \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02',
            ),
        );

        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier code', $supplier->label());
    }

    /** @test */
    public function itCreatesAndFindsASupplierByItsCode(): void
    {
        $supplierRepository = new InMemoryRepository();

        $supplierRepository->save(
            \Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier::create(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'supplier_code',
                'Supplier code',
                [],
            ),
        );

        $supplier = $supplierRepository->findByCode(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString('supplier_code'));

        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier code', $supplier->label());
    }

    /** @test */
    public function itUpdatesASupplier(): void
    {
        $supplierRepository = new InMemoryRepository();

        $supplierRepository->save(
            \Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier::create(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'new_supplier_code',
                'New supplier code',
                [],
            ),
        );

        $supplier = $supplierRepository->find(
            \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02',
            ),
        );

        static::assertSame('new_supplier_code', $supplier->code());
        static::assertSame('New supplier code', $supplier->label());
    }

    /** @test */
    public function itReturnsNullWhenASupplierCannotBeFound(): void
    {
        static::assertNull(
            (new InMemoryRepository())
                ->find(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')),
        );
    }

    /** @test */
    public function itDeletesASupplier(): void
    {
        $supplierRepository = new InMemoryRepository();
        $identifier = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');
        $supplierRepository->save(
            \Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier::create(
                (string) $identifier,
                'supplier_code',
                'Supplier code',
                [],
            ),
        );
        $supplierRepository->save(
            \Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier::create(
                '44ce8069-8da1-4986-872f-311737f46f01',
                'supplier_code2',
                'Supplier code2',
                [],
            ),
        );
        $supplierRepository->delete($identifier);

        $this->assertNull($supplierRepository->find($identifier));
        $this->assertInstanceOf(
            \Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier::class,
            $supplierRepository->find(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f01')),
        );
    }
}
