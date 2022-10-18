<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\Authentication\ContributorAccount\Repository\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itSavesAContributorAccount(): void
    {
        $repository = $this->get(ContributorAccountRepository::class);
        $contributorAccount = ContributorAccount::fromEmail('burger@example.com');
        $contributorAccount->setPassword('P@$$w0rd');
        $repository->save($contributorAccount);
        $repository->save(ContributorAccount::fromEmail('jambon@example.com'));

        $savedContributorAccount = $this->findContributorAccount('burger@example.com');

        $this->assertSame($contributorAccount->email(), $savedContributorAccount['email']);
        $this->assertSame($contributorAccount->identifier(), $savedContributorAccount['id']);
        $this->assertSame($contributorAccount->accessToken(), $savedContributorAccount['access_token']);
        $this->assertSame($contributorAccount->createdAt(), $savedContributorAccount['created_at']);
        $this->assertSame($contributorAccount->accessTokenCreatedAt(), $savedContributorAccount['access_token_created_at']);
        $this->assertNotNull($savedContributorAccount['password']);
        $this->assertFalse($savedContributorAccount['consent']);
    }

    /** @test */
    public function itSavesAContributorAccountConsent(): void
    {
        $repository = $this->get(ContributorAccountRepository::class);
        $contributorAccount = ContributorAccount::fromEmail('burger@example.com');
        $contributorAccount->consent();
        $repository->save($contributorAccount);

        $savedContributorAccount = $this->findContributorAccount('burger@example.com');

        $this->assertTrue($savedContributorAccount['consent']);
    }

    /** @test */
    public function itFindsAContributorAccountByItsId(): void
    {
        $repository = $this->get(ContributorAccountRepository::class);
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'contributor@example.com',
            '2022-06-06 12:52:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-06 12:52:44',
            '2022-06-06 12:52:44',
            true,
        );
        $repository->save($contributorAccount);
        $repository->save(ContributorAccount::fromEmail('jambon@example.com'));

        static::assertSame(
            'contributor@example.com',
            $repository->find(Identifier::fromString($contributorAccount->identifier()))->email(),
        );
    }

    /** @test */
    public function itFindsAContributorAccountByItsEmail(): void
    {
        $repository = $this->get(ContributorAccountRepository::class);
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'contributor@example.com',
            '2022-06-28 11:10:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-28 11:10:44',
            '2022-06-28 11:10:44',
            true,
        );
        $repository->save($contributorAccount);
        $repository->save(ContributorAccount::fromEmail('contributor2@example.com'));

        static::assertSame(
            'contributor@example.com',
            $repository->findByEmail(Email::fromString($contributorAccount->email()))->email(),
        );
    }

    /** @test */
    public function itReturnsNullIfAContributorAccountCannotBeFindByItsId(): void
    {
        $repository = $this->get(ContributorAccountRepository::class);
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'contributor@example.com',
            '2022-06-06 12:52:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-06 12:52:44',
            '2022-06-06 12:52:44',
            true,
        );
        $repository->save($contributorAccount);

        static::assertNull(
            $repository->find(Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2')),
        );
    }

    /** @test */
    public function itDeletesAContributorAccountByEmail(): void
    {
        $repository = $this->get(ContributorAccountRepository::class);
        $repository->save(ContributorAccount::fromEmail('contributor1@example.com'));
        $repository->save(ContributorAccount::fromEmail('contributor2@example.com'));
        $repository->save(ContributorAccount::fromEmail('contributor3@example.com'));

        $repository->deleteByEmail('contributor2@example.com');

        $this->assertNull($this->findContributorAccount('contributor2@example.com'));
        $this->assertIsArray($this->findContributorAccount('contributor1@example.com'));
        $this->assertIsArray($this->findContributorAccount('contributor3@example.com'));
    }

    private function findContributorAccount(string $email): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM `akeneo_supplier_portal_contributor_account`
            WHERE email = :email
        SQL;

        $contributorAccount = $this->get(Connection::class)
            ->executeQuery($sql, ['email' => $email])
            ->fetchAssociative()
        ;

        if (false !== $contributorAccount) {
            $contributorAccount['consent'] = (bool) $contributorAccount['consent'];
        }

        return $contributorAccount ?: null;
    }
}
