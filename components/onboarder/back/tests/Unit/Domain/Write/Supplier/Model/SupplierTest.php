<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\Model;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event\ContributorDeleted;
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

    /** @test */
    public function itStoresAContributorAddedDomainEventWhenCreatingASupplierWithAContributor(): void
    {
        $supplier = Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier label',
            ['foo@foo.foo'],
        );

        $expectedContributorAddedEvent = $supplier->events()[0];

        static::assertInstanceOf(ContributorAdded::class, $expectedContributorAddedEvent);
        static::assertSame('foo@foo.foo', $expectedContributorAddedEvent->contributorEmail());
        static::assertSame(
            '44ce8069-8da1-4986-872f-311737f46f02',
            (string) $expectedContributorAddedEvent->supplierIdentifier(),
        );
        // Check that there is no events anymore in the supplier object
        static::assertCount(0, $supplier->events());
    }

    /** @test */
    public function itStoresAContributorDeletedAndAContributorAddedDomainEventsWhenUpdatingASupplierAfterRemovingAContributorAndAddingANewOne(): void
    {
        $supplier = Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier label',
            ['foo@foo.foo'],
        );
        $supplier->events();

        $supplier->update('Supplier label', ['bar@bar.bar']);

        $expectedContributorDeletedEvents = $supplier->events();

        static::assertInstanceOf(ContributorDeleted::class, $expectedContributorDeletedEvents[0]);
        static::assertSame('foo@foo.foo', $expectedContributorDeletedEvents[0]->contributorEmail());
        static::assertSame(
            '44ce8069-8da1-4986-872f-311737f46f02',
            (string) $expectedContributorDeletedEvents[0]->supplierIdentifier(),
        );
        static::assertInstanceOf(ContributorAdded::class, $expectedContributorDeletedEvents[1]);
        static::assertSame('bar@bar.bar', $expectedContributorDeletedEvents[1]->contributorEmail());
        static::assertSame(
            '44ce8069-8da1-4986-872f-311737f46f02',
            (string) $expectedContributorDeletedEvents[1]->supplierIdentifier(),
        );
        // Check that there is no events anymore in the supplier object
        static::assertCount(0, $supplier->events());
    }
}
