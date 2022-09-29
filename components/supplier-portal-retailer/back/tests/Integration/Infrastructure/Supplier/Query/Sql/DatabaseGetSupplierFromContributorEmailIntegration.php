<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetSupplierFromContributorEmailIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfSupplierDoesNotHaveContributor(): void
    {
        ($this->get(Repository::class))->save((new SupplierBuilder())->build());

        static::assertNull(($this->get(GetSupplierFromContributorEmail::class))('contributor1@example.com'));
    }

    /** @test */
    public function itReturnsNullIfContributorDoesNotExist(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withContributors(['contributor1@example.com'])
                ->build(),
        );

        static::assertNull(($this->get(GetSupplierFromContributorEmail::class))('contributor2@example.com'));
    }

    /** @test */
    public function itGetsASupplierFromContributorEmail(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->withContributors(['contributor1@example.com'])
                ->build(),
        );

        $supplier = ($this->get(GetSupplierFromContributorEmail::class))('contributor1@example.com');

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier label', $supplier->label);
    }
}
