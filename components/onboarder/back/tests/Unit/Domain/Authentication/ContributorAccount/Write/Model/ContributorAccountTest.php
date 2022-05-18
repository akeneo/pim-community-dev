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
        $this->assertTrue($contributorAccount->getEmail()->equals(Email::fromString('contributor@example.com')));
        $this->assertNull($contributorAccount->getPassword());
        $this->assertNull($contributorAccount->getLastLoggedAt());
        $this->assertInstanceOf(AccessToken::class, $contributorAccount->getAccessToken());
        $this->assertInstanceOf(\DateTimeImmutable::class, $contributorAccount->getCreatedAt());
    }
}
