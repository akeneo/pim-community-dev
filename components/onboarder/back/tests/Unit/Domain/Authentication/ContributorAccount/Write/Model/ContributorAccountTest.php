<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Authentication\ContributorAccount\Write\Model;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;

final class ContributorAccountTest extends TestCase
{
    /** @test */
    public function itCreatesAContributorAccount(): void
    {
        $contributorAccount = ContributorAccount::fromEmail('contributor@example.com');
        $this->assertEquals('contributor@example.com', $contributorAccount->email());
        $this->assertNull($contributorAccount->password());
        $this->assertNull($contributorAccount->lastLoggedAt());
        $this->assertIsString($contributorAccount->accessToken());
        $this->assertIsString($contributorAccount->createdAt());
        $this->assertIsString($contributorAccount->accessTokenCreatedAt());
    }
}
