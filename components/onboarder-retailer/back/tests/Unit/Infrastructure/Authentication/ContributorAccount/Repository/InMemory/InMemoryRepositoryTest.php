<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Infrastructure\Authentication\ContributorAccount\Repository\InMemory;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itSavesAndFindsAContributorAccountByItsEmail(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $contributorAccountRepository->save(ContributorAccount::fromEmail('momoss@example.com'));
        $contributorAccountRepository->save(ContributorAccount::fromEmail('contributor@example.com'));

        $contributorAccount = $contributorAccountRepository->findByEmail('momoss@example.com');

        $this->assertSame($contributorAccount->email(), 'momoss@example.com');
    }

    /** @test */
    public function itReturnsNullWhenContributorDoesNotExists(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $contributorAccountRepository->save(ContributorAccount::fromEmail('momoss@example.com'));

        $this->assertNull($contributorAccountRepository->findByEmail('yolo@example.com'));
    }

    /** @test */
    public function itFindsAContributorAccountByItsId(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $testContributor = ContributorAccount::fromEmail('test@example.com');
        $contributorAccountRepository->save($testContributor);
        $contributorAccountRepository->save(ContributorAccount::fromEmail('contributor@example.com'));

        $contributorAccount = $contributorAccountRepository->find(
            Identifier::fromString($testContributor->identifier()),
        );

        static::assertSame($contributorAccount->email(), 'test@example.com');
    }

    /** @test */
    public function itReturnsNullIfAContributorAccountCannotBeFindByItsId(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $testContributor = ContributorAccount::fromEmail('test@example.com');
        $contributorAccountRepository->save($testContributor);

        $contributorAccount = $contributorAccountRepository->find(
            Identifier::fromString('9e42ded3-1085-441e-b89c-b4681602ac10'),
        );

        static::assertNull($contributorAccount);
    }
}
