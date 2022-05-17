<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\Event;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;
use PHPUnit\Framework\TestCase;

final class ContributorAddedTest extends TestCase
{
    /** @test */
    public function itContainsTheSupplierIdentifierAndTheContributorEmail(): void
    {
        $contributorAddedEvent = new ContributorAdded(
            Identifier::fromString('5781bb4b-a3c6-4224-b6b3-eccd73f669b1'),
            'foo@foo.foo',
        );

        static::assertSame(
            '5781bb4b-a3c6-4224-b6b3-eccd73f669b1',
            (string) $contributorAddedEvent->supplierIdentifier(),
        );
        static::assertSame(
            'foo@foo.foo',
            $contributorAddedEvent->contributorEmail(),
        );
    }
}
