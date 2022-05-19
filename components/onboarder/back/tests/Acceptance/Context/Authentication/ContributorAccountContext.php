<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Acceptance\Context\Authentication;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class ContributorAccountContext implements Context
{
    public function __construct(private InMemoryRepository $contributorAccountRepository)
    {
    }

    /**
     * @Then I should have ":contributorAccountEmails" contributor accounts
     */
    public function iShouldHaveContributorAccounts(string $contributorAccountEmails)
    {
        $emails = explode(';', $contributorAccountEmails);

        $contributorAccount0 = $this->contributorAccountRepository->findByEmail($emails[0]);
        $contributorAccount1 = $this->contributorAccountRepository->findByEmail($emails[1]);

        Assert::assertSame($emails[0],(string) $contributorAccount0->email());
        Assert::assertSame($emails[1],(string) $contributorAccount1->email());

        $this->assertContributorAccountIsValid($contributorAccount0);
        $this->assertContributorAccountIsValid($contributorAccount1);
    }

    private function assertContributorAccountIsValid(ContributorAccount $contributorAccount)
    {
        Assert::assertNotNull($contributorAccount->accessToken());
        Assert::assertNotNull($contributorAccount->accessTokenCreatedAt());
        Assert::assertNotNull($contributorAccount->createdAt());
    }
}
