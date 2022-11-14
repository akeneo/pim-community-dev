<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\Supplier\Write\Event;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use PHPUnit\Framework\TestCase;

final class ContributorDeletedTest extends TestCase
{
    /** @test */
    public function itContainsTheSupplierIdentifierAndTheContributorEmail(): void
    {
        $contributorDeletedEvent = new ContributorDeleted(
            Identifier::fromString('5781bb4b-a3c6-4224-b6b3-eccd73f669b1'),
            'foo@foo.foo',
        );

        static::assertSame(
            '5781bb4b-a3c6-4224-b6b3-eccd73f669b1',
            (string) $contributorDeletedEvent->supplierIdentifier(),
        );
        static::assertSame(
            'foo@foo.foo',
            $contributorDeletedEvent->contributorEmail(),
        );
    }
}
