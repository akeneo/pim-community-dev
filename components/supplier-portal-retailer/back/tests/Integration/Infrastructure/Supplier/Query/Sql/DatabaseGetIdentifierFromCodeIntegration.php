<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetIdentifierFromCode;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetIdentifierFromCodeIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsIdentifierFromCode(): void
    {
        $supplier = (new SupplierBuilder())
            ->withIdentifier('a3aac0e2-9eb9-4203-8af2-5425b2062ad4')
            ->build();
        ($this->get(Repository::class))->save($supplier);

        $supplierIdentifier = ($this->get(GetIdentifierFromCode::class))('supplier_code');

        static::assertSame('a3aac0e2-9eb9-4203-8af2-5425b2062ad4', $supplierIdentifier);
    }

    /** @test */
    public function itReturnsNullIfThereIsNoSupplierForTheGivenCode(): void
    {
        ($this->get(Repository::class))->save((new SupplierBuilder())->build());

        $supplierIdentifier = ($this->get(GetIdentifierFromCode::class))('unknown_supplier_code');

        static::assertNull($supplierIdentifier);
    }
}
