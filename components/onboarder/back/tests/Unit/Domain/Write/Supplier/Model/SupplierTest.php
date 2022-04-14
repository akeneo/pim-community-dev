<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\Model;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier;
use PHPUnit\Framework\TestCase;

final class SupplierTest extends TestCase
{
    /** @test */
    public function itCreatesASupplier(): void
    {
        $supplier = Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code',
            [],
        );

        static::assertInstanceOf(Supplier::class, $supplier);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier());
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier code', $supplier->label());
    }
}
