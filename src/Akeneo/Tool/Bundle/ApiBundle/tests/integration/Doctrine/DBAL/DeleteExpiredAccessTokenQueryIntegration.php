<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Doctrine\DBAL;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Doctrine\DBAL\DeleteExpiredAccessTokenQuery;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Tool\Bundle\ApiBundle\Doctrine\DBAL\DeleteExpiredAccessTokenQuery
 */
class DeleteExpiredAccessTokenQueryIntegration extends ApiTestCase
{
    private ?ClientManagerInterface $clientManager;
    private ?Connection $connection;
    private ?DeleteExpiredAccessTokenQuery $deleteExpiredAccessTokenQuery;
    protected function setUp(): void
    {
        parent::setUp();

        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
        $this->connection = $this->get('database_connection');
        $this->deleteExpiredAccessTokenQuery = $this->get(DeleteExpiredAccessTokenQuery::class);

    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItDeletesExpiredAccessToken(): void
    {
        $this->create5ExpiredAccessTokens();
        $this->create5ValidAccessTokens();

        $this->assertValidAccessTokenCount(5);
        $this->assertExpiredAccessTokenCount(5);

        $this->deleteExpiredAccessTokenQuery->execute();

        $this->assertValidAccessTokenCount(5);
        $this->assertExpiredAccessTokenCount(0);
    }

    private function create5ExpiredAccessTokens(): void
    {
        $userId = $this->createUser('user_for_expired_tokens');
        $clientId = $this->createFosAuthClient();
        $expiredTimestamp = \time() - 100;

        $this->connection->executeStatement(<<<SQL
            INSERT INTO pim_api_access_token (`client`, `user`, `token`, `expires_at`) VALUES
                ($clientId, $userId, 'invalid_token_1', $expiredTimestamp),
                ($clientId, $userId, 'invalid_token_2', $expiredTimestamp),
                ($clientId, $userId, 'invalid_token_3', $expiredTimestamp),
                ($clientId, $userId, 'invalid_token_4', $expiredTimestamp),
                ($clientId, $userId, 'invalid_token_5', $expiredTimestamp)
        SQL);
    }

    private function create5ValidAccessTokens(): void
    {
        $userId = $this->createUser('user_for_valid_tokens');
        $clientId = $this->createFosAuthClient();
        $validTimestamp = \time() + 100;

        $this->connection->executeStatement(<<<SQL
            INSERT INTO pim_api_access_token (`client`, `user`, `token`, `expires_at`) VALUES
                ($clientId, $userId, 'valid_token_1', $validTimestamp),
                ($clientId, $userId, 'valid_token_2', $validTimestamp),
                ($clientId, $userId, 'valid_token_3', $validTimestamp),
                ($clientId, $userId, 'valid_token_4', $validTimestamp),
                ($clientId, $userId, 'valid_token_5', $validTimestamp)
        SQL);
    }

    private function createFosAuthClient(): int
    {
        /** @var Client $fosClient */
        $fosClient = $this->clientManager->createClient();
        $fosClient->setLabel('test_client');

        $this->clientManager->updateClient($fosClient);

        $sqlQuery = "SELECT id FROM pim_api_client WHERE label = 'test_client'";

        return (int) $this->connection->executeQuery($sqlQuery)->fetchOne();
    }

    private function createUser(string $username): int
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setPassword('password');
        $user->setEmail($username.'@example.com');

        $this->get('validator')->validate($user);
        $this->get('pim_user.saver.user')->save($user);

        return $user->getId();
    }

    private function assertValidAccessTokenCount(int $expected): void
    {
        $validAccessTokenCount = (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM pim_api_access_token WHERE expires_at >= :now_timestamp',
            ['now_timestamp' =>  \time()],
            ['now_timestamp' =>  ParameterType::INTEGER]
        )->fetchOne();

        $this->assertEquals($expected, $validAccessTokenCount, 'Valid access token count should match');
    }

    private function assertExpiredAccessTokenCount(int $expected): void
    {
        $expiredAccessTokenCount = (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM pim_api_access_token WHERE expires_at < :now_timestamp',
            ['now_timestamp' =>  \time()],
            ['now_timestamp' =>  ParameterType::INTEGER]
        )->fetchOne();

        $this->assertEquals($expected, $expiredAccessTokenCount, 'Expired access token count should match');
    }
}
