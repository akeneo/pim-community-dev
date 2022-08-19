<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Infrastructure\Authentication\ContributorAccount\Repository\InMemory;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itSavesAndFindsAContributorAccountByItsEmail(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $contributorAccountRepository->save(ContributorAccount::fromEmail('momoss@example.com'));
        $contributorAccountRepository->save(ContributorAccount::fromEmail('contributor@example.com'));

        $contributorAccount = $contributorAccountRepository->findByEmail(Email::fromString('momoss@example.com'));

        $this->assertSame($contributorAccount->email(), 'momoss@example.com');
    }

    /** @test */
    public function itReturnsNullWhenContributorDoesNotExists(): void
    {
        $contributorAccountRepository = new InMemoryRepository();

        $contributorAccountRepository->save(ContributorAccount::fromEmail('momoss@example.com'));

        $this->assertNull($contributorAccountRepository->findByEmail(Email::fromString('yolo@example.com')));
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

    /** @test */
    public function itDeletesAContributorAccountByEmail(): void
    {
        $repository = new InMemoryRepository();
        $repository->save(ContributorAccount::fromEmail('contributor1@example.com'));
        $repository->save(ContributorAccount::fromEmail('contributor2@example.com'));
        $repository->save(ContributorAccount::fromEmail('contributor3@example.com'));

        $repository->deleteByEmail('contributor2@example.com');

        $this->assertNull($repository->findByEmail(Email::fromString('contributor2@example.com')));
        $this->assertInstanceOf(ContributorAccount::class, $repository->findByEmail(Email::fromString('contributor1@example.com')));
        $this->assertInstanceOf(ContributorAccount::class, $repository->findByEmail(Email::fromString('contributor3@example.com')));
    }
}
