<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Authentication\ContributorAccount\Repository\InMemory;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itCreatesAndFindsAContributorAccount(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $contributorAccountRepository->save(ContributorAccount::fromEmail('momoss@example.com'));

        $contributorAccount = $contributorAccountRepository->findByEmail('momoss@example.com');

        $this->assertSame($contributorAccount->email(), 'momoss@example.com');
    }
}
