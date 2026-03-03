<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Handler;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\Handler\DeleteExpiredTokensHandler;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Tool\Bundle\ApiBundle\Handler\DeleteExpiredTokensHandler
 */
class DeleteExpiredTokensHandlerIntegration extends ApiTestCase
{
    private ?ClientManagerInterface $clientManager;
    private ?Connection $connection;
    private ?DeleteExpiredTokensHandler $deleteExpiredTokensHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
        $this->connection = $this->get('database_connection');
        $this->deleteExpiredTokensHandler = $this->get(DeleteExpiredTokensHandler::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItDeletesExpiredTokens(): void
    {
        $userId = $this->createUser('token_user');
        $clientId = $this->createFosAuthClient();
        $expiredTimestamp = \time() - 100;

        $this->connection->insert('pim_api_access_token', [
            'client' => $clientId,
            'user' => $userId,
            'token' => 'expired_access_token',
            'expires_at' => $expiredTimestamp,
        ]);

        $this->connection->insert('pim_api_refresh_token', [
            'client' => $clientId,
            'user' => $userId,
            'token' => 'expired_refresh_token',
            'expires_at' => $expiredTimestamp,
        ]);

        $this->assertTrue($this->accessTokenExists('expired_access_token'));
        $this->assertTrue($this->refreshTokenExists('expired_refresh_token'));

        $this->deleteExpiredTokensHandler->handle();

        $this->assertFalse($this->accessTokenExists('expired_access_token'));
        $this->assertFalse($this->refreshTokenExists('expired_refresh_token'));
    }

    private function createFosAuthClient(): int
    {
        /** @var Client $fosClient */
        $fosClient = $this->clientManager->createClient();
        $fosClient->setLabel('test_client');

        $this->clientManager->updateClient($fosClient);

        $sqlQuery = "SELECT id FROM pim_api_client WHERE label = 'test_client'";

        return (int)$this->connection->executeQuery($sqlQuery)->fetchOne();
    }

    private function createUser(string $username): int
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setPassword('password');
        $user->setEmail($username . '@example.com');

        $this->get('validator')->validate($user);
        $this->get('pim_user.saver.user')->save($user);

        return $user->getId();
    }

    private function accessTokenExists(string $token): bool
    {
        $token = $this->connection->executeQuery(
            'SELECT id FROM pim_api_access_token WHERE token = :token',
            ['token' => $token],
        )->fetchOne();

        return false !== $token;
    }

    private function refreshTokenExists(string $token): bool
    {
        $token = $this->connection->executeQuery(
            'SELECT id FROM pim_api_refresh_token WHERE token = :token',
            ['token' => $token],
        )->fetchOne();

        return false !== $token;
    }
}
