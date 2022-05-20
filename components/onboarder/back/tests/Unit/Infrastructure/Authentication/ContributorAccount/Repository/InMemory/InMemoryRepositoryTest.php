<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Authentication\ContributorAccount\Repository\InMemory;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itSavesAndFindsAContributorAccount(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $contributorAccountRepository->save(ContributorAccount::fromEmail('momoss@example.com'));
        $contributorAccountRepository->save(ContributorAccount::fromEmail('contributor@example.com'));

        $contributorAccount = $contributorAccountRepository->findByEmail('momoss@example.com');

        $this->assertSame($contributorAccount->email(), 'momoss@example.com');
    }

    /** @test */
    public function itReturnNullWhenContributorDoesNotExist(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $contributorAccountRepository->save(ContributorAccount::fromEmail('momoss@example.com'));

        $this->assertNull($contributorAccountRepository->findByEmail('yolo@example.com'));
    }
}
