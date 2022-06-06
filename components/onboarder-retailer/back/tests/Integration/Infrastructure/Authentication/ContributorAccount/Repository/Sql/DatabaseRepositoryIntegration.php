<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Integration\Infrastructure\Authentication\ContributorAccount\Repository\Sql;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itSavesAContributorAccount(): void
    {
        $repository = $this->get(ContributorAccountRepository::class);
        $contributorAccount = ContributorAccount::fromEmail('burger@example.com');
        $repository->save($contributorAccount);
        $repository->save(ContributorAccount::fromEmail('jambon@example.com'));

        $savedContributorAccount = $this->findContributorAccount('burger@example.com');

        $this->assertSame($contributorAccount->email(), $savedContributorAccount['email']);
        $this->assertSame($contributorAccount->identifier(), $savedContributorAccount['id']);
        $this->assertSame($contributorAccount->accessToken(), $savedContributorAccount['access_token']);
        $this->assertSame($contributorAccount->createdAt(), $savedContributorAccount['created_at']);
        $this->assertSame($contributorAccount->accessTokenCreatedAt(), $savedContributorAccount['access_token_created_at']);
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
        );
        $repository->save($contributorAccount);
        $repository->save(ContributorAccount::fromEmail('jambon@example.com'));

        static::assertSame(
            'contributor@example.com',
            $repository->find(Identifier::fromString($contributorAccount->identifier()))->email(),
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
        );
        $repository->save($contributorAccount);

        static::assertNull(
            $repository->find(Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2')),
        );
    }

    private function findContributorAccount(string $email): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM `akeneo_onboarder_serenity_contributor_account`
            WHERE email = :email
        SQL;

        $contributorAccount = $this->get(Connection::class)
            ->executeQuery($sql, ['email' => $email])
            ->fetchAssociative()
        ;

        return $contributorAccount ?: null;
    }
}
