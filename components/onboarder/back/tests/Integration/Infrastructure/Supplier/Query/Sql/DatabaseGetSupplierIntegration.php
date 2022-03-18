<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Repository;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Code;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetSupplierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfThereIsNoSupplier(): void
    {
        static::assertNull(($this->get(GetSupplier::class))(
            Code::fromString('unknown_supplier')
        ));
    }

    /** @test */
    public function itGetsASupplier(): void
    {
        $this->createSupplier();

        $supplier = ($this->get(GetSupplier::class))(
            Code::fromString('supplier_code')
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier());
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier code', $supplier->label());
    }

    private function createSupplier(): void
    {
        $this->get(Repository::class)->save(Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code'
        ));
    }
}
