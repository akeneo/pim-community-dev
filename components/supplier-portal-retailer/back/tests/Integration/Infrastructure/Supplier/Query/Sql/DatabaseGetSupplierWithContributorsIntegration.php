<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetSupplierWithContributorsIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfThereIsNoSupplier(): void
    {
        static::assertNull(($this->get(GetSupplierWithContributors::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        ));
    }

    /** @test */
    public function itGetsASupplierWithContributors(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->withContributors([
                    'contributor1@example.com',
                    'contributor2@example.com',
                ])
                ->build(),
        );

        $supplier = ($this->get(GetSupplierWithContributors::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier label', $supplier->label);
        static::assertEquals(
            [
                'contributor1@example.com',
                'contributor2@example.com',
            ],
            array_values($supplier->contributors),
        );
    }

    /** @test */
    public function itGetsASupplierWithNoContributors(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->build(),
        );

        $supplier = ($this->get(GetSupplierWithContributors::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier label', $supplier->label);
        static::assertSame([], $supplier->contributors);
    }
}
