<?php

declare(strict_types=1);

namespace Akeneo\Onboarder\Test\Unit\Domain\Supplier;

use Akeneo\Onboarder\Domain\Supplier\Code;
use Akeneo\Onboarder\Domain\Supplier\Identifier;
use Akeneo\Onboarder\Domain\Supplier\Label;
use Akeneo\Onboarder\Domain\Supplier\Supplier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SupplierTest extends KernelTestCase
{
    /** @test */
    public function itCreatesASupplierAndCanAccessItsProperties(): void
    {
        $supplier = Supplier::create(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'),
            Code::fromString('supplier_code'),
            Label::fromString('Supplier code')
        );

        static::assertInstanceOf(Supplier::class, $supplier);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $supplier->getIdentifier());
        static::assertSame('supplier_code', (string) $supplier->getCode());
        static::assertSame('Supplier code', (string) $supplier->getLabel());
    }

    /** @test */
    public function itCanCompareSupplierInstances(): void
    {
        $supplier = Supplier::create(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'),
            Code::fromString('supplier_code'),
            Label::fromString('Supplier code')
        );

        $supplierWithDifferentIdentifier = Supplier::create(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f01'),
            Code::fromString('supplier_code'),
            Label::fromString('Supplier code')
        );

        $supplierWithDifferentCode = Supplier::create(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'),
            Code::fromString('different_supplier_code'),
            Label::fromString('Supplier code')
        );

        $supplierWithDifferentLabel = Supplier::create(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f01'),
            Code::fromString('supplier_code'),
            Label::fromString('Different supplier code')
        );

        static::assertTrue($supplier->equals($supplier));
        static::assertFalse($supplier->equals($supplierWithDifferentIdentifier));
        static::assertFalse($supplier->equals($supplierWithDifferentCode));
        static::assertFalse($supplier->equals($supplierWithDifferentLabel));
    }
}
