<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;

class Version_7_0_20220513000000_increase_scope_length_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220513000000_increase_scope_length';

    // 300 bytes, it's more than the 255 default limit
    private const SCOPE_TOO_LONG = <<<TXT
Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Sed elementum lectus eget ante auctor tincidunt eget id risus.
Nullam metus dui, fringilla at lorem eget, aliquet mollis tortor.
Donec ac egestas quam. In hac habitasse platea dictumst. 
Cras sit amet turpis consequat, pulvinar erat sed lectus.
TXT;

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItChangesTheScopeColumnLength(): void
    {
        $this->setScopeColumnLengthToFOSOAuthDefaultValue();
        $this->assertAccessTokenWithLongScopeCannotBeCreated();

        $this->insertOAuthClient();
        $this->insertAccessTokenWithScopes('lorem ipsum dolor sit amet');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertAccessTokenWithScopesStillExists('lorem ipsum dolor sit amet');
        $this->assertAccessTokenWithLongScopeCanBeCreated();
    }

    private function setScopeColumnLengthToFOSOAuthDefaultValue(): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $connection->executeQuery("ALTER TABLE pim_api_access_token MODIFY scope VARCHAR(255) DEFAULT NULL");
    }

    private function insertOAuthClient(): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $connection->insert('pim_api_client', [
            'id' => 1,
            'random_id' => 'foo',
            'redirect_uris' => '',
            'secret' => 'bar',
            'allowed_grant_types' => '',
        ]);
    }

    private function insertAccessTokenWithScopes(string $scope): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $uid = $connection->executeQuery('SELECT id FROM oro_user WHERE username = "admin"')->fetchOne();

        $connection->insert('pim_api_access_token', [
            'client' => 1,
            'user' => $uid,
            'token' => rand(),
            'expires_at' => null,
            'scope' => $scope,
        ]);
    }

    private function assertAccessTokenWithScopesStillExists(string $scope): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $count = $connection->executeQuery('SELECT COUNT(*) FROM pim_api_access_token WHERE scope = :scope', [
            'scope' => $scope,
        ])->fetchOne();

        $this->assertEquals(1, $count);
    }

    private function assertAccessTokenWithLongScopeCanBeCreated(): void
    {
        $this->insertAccessTokenWithScopes(self::SCOPE_TOO_LONG);
    }

    private function assertAccessTokenWithLongScopeCannotBeCreated(): void
    {
        try {
            $this->insertAccessTokenWithScopes(self::SCOPE_TOO_LONG);
        } catch (DriverException $e) {
            return;
        }

        $this->fail('No error when inserting a scope too long');
    }
}
