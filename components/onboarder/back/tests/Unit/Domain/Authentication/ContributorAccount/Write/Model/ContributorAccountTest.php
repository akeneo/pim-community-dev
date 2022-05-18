<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Authentication\ContributorAccount\Write\Model;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject\AccessToken;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class ContributorAccountTest extends TestCase
{
    /** @test */
    public function itCreatesAContributorAccount(): void
    {
        $contributorAccount = ContributorAccount::fromEmail('contributor@example.com');
        $this->assertTrue($contributorAccount->email()->equals(Email::fromString('contributor@example.com')));
        $this->assertNull($contributorAccount->password());
        $this->assertNull($contributorAccount->lastLoggedAt());
        $this->assertInstanceOf(AccessToken::class, $contributorAccount->accessToken());
        $this->assertInstanceOf(\DateTimeImmutable::class, $contributorAccount->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $contributorAccount->accessTokenCreatedAt());
    }
}
