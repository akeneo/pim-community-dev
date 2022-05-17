<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\Model;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;

final class ContributorAccountTest extends TestCase
{
    /** @test */
    public function itCreatesAContributorAccount(): void
    {
        $contributorAccount = ContributorAccount::fromEmail('contributor@example.com');
        $this->assertSame('contributor@example.com', $contributorAccount->getEmail());
        $this->assertSame('azerty', $contributorAccount->getPassword());
        $this->assertNull($contributorAccount->getLastLoggedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $contributorAccount->getCreatedAt());
    }
}
