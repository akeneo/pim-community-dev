<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryGetSupplierTest extends TestCase
{
    /** @test */
    public function itReturnsNullIfThereIsNoSupplier(): void
    {
        $sut = new InMemoryGetSupplier(new InMemoryRepository());

        static::assertNull(($sut)(Identifier::fromString('ca8baefd-0e05-4683-be48-6b9ff87e4cbc')));
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
                'Supplier label',
                ['foo@foo.bar', 'foo2@foo2.bar'],
            ),
        );

        $supplier = ($sut)(Identifier::fromString('ca8baefd-0e05-4683-be48-6b9ff87e4cbc'));

        static::assertSame('ca8baefd-0e05-4683-be48-6b9ff87e4cbc', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier label', $supplier->label);
        static::assertCount(2, $supplier->contributors);
        static::assertSame('foo@foo.bar', $supplier->contributors[0]['email']);
        static::assertSame('foo2@foo2.bar', $supplier->contributors[1]['email']);
    }
}
