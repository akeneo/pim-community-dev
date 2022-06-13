<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Domain\Write\Supplier\Event;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
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
