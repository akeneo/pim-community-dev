<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\Supplier\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use PHPUnit\Framework\TestCase;

final class SupplierTest extends TestCase
{
    /** @test */
    public function itCreatesASupplier(): void
    {
        $supplier = (new SupplierBuilder())
            ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
            ->build();

        static::assertInstanceOf(Supplier::class, $supplier);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier());
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier label', $supplier->label());
    }

    /** @test */
    public function itStoresAContributorDeletedAndAContributorAddedDomainEventsWhenUpdatingASupplierAfterRemovingAContributorAndAddingANewOne(): void
    {
        $supplier = (new SupplierBuilder())
            ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
            ->withContributors(['foo@foo.foo'])
            ->build();
        $supplier->events();

        $supplier->update('Supplier label', ['bar@bar.bar']);

        $expectedEvents = $supplier->events();

        static::assertInstanceOf(ContributorDeleted::class, $expectedEvents[0]);
        static::assertInstanceOf(ContributorAdded::class, $expectedEvents[1]);
        // Check that there is no events anymore in the supplier object
        static::assertCount(0, $supplier->events());
    }
}
