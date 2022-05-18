<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Authentication\ContributorAccount\Repository\Sql;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itSavesASupplier(): void
    {
        $repository = $this->get(ContributorAccountRepository::class);
        $contributorAccount = ContributorAccount::fromEmail('burger@example.com');
        $repository->save($contributorAccount);
        $repository->save(ContributorAccount::fromEmail('jambon@example.com'));

        $savedContributorAccount = $this->findContributorAccount('burger@example.com');

        $this->assertSame((string) $contributorAccount->email(), $savedContributorAccount['email']);
        $this->assertSame((string) $contributorAccount->identifier(), $savedContributorAccount['id']);
        $this->assertSame((string) $contributorAccount->accessToken(), $savedContributorAccount['access_token']);
        $this->assertSame($contributorAccount->createdAt()->format('Y-m-d H:i:s'), $savedContributorAccount['created_at']);
        $this->assertSame($contributorAccount->accessTokenCreatedAt()->format('Y-m-d H:i:s'), $savedContributorAccount['access_token_created_at']);
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
