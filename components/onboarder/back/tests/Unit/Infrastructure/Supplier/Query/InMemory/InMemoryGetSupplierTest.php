<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Code;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryGetSupplierTest extends TestCase
{
    /** @test */
    public function itReturnsNullIfThereIsNoSupplier(): void
    {
        $sut = new InMemoryGetSupplier(new InMemoryRepository());

        static::assertNull(($sut)(Code::fromString('unknown_supplier')));
    }

    /** @test */
    public function itGetsASupplier(): void
    {
        $supplierRepository = new InMemoryRepository();
        $sut = new InMemoryGetSupplier($supplierRepository);

        $supplierRepository->save(
            Supplier::create(
                'ca8baefd-0e05-4683-be48-6b9ff87e4cbc',
                'supplier_code',
                'Supplier label'
            )
        );

        $supplier = ($sut)(Code::fromString('supplier_code'));

        static::assertSame('ca8baefd-0e05-4683-be48-6b9ff87e4cbc', $supplier->identifier());
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier label', $supplier->label());
    }
}
