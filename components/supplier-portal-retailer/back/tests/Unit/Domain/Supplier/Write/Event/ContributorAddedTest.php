<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\Supplier\Write\Event;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use PHPUnit\Framework\TestCase;

final class ContributorAddedTest extends TestCase
{
    /** @test */
    public function itContainsTheSupplierIdentifierAndTheContributorEmail(): void
    {
        $contributorAddedEvent = new ContributorAdded(
            Identifier::fromString('5781bb4b-a3c6-4224-b6b3-eccd73f669b1'),
            'foo@foo.foo',
            'los_pollos_hermanos',
            new \DateTimeImmutable(),
        );

        static::assertSame(
            '5781bb4b-a3c6-4224-b6b3-eccd73f669b1',
            (string) $contributorAddedEvent->supplierIdentifier(),
        );
        static::assertSame(
            'foo@foo.foo',
            $contributorAddedEvent->contributorEmail(),
        );
        static::assertSame(
            'los_pollos_hermanos',
            $contributorAddedEvent->supplierCode(),
        );
    }
}
