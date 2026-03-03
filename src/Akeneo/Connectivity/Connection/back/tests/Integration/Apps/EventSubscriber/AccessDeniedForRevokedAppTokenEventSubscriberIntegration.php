<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AccessDeniedForRevokedAppTokenEventSubscriberIntegration extends ApiTestCase
{
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
    }

    public function test_it_throw_an_access_denied_http_exception_with_custom_message_when_used_access_token_is_revoked(): void
    {
        $this->saveRevokedAccessToken('revoked_access_token');

        $client = $this->createClientWithAccessToken('revoked_access_token');
        $client->request('GET', '/api/rest/v1/products');

        Assert::assertEquals(401, $client->getResponse()->getStatusCode());
        Assert::assertEquals(\json_encode([
            'code' => 401,
            'message' => 'The access token provided is invalid. Your app has been disconnected from that PIM.',
        ]), $client->getResponse()->getContent());
    }

    private function createClientWithAccessToken(string $accessToken): KernelBrowser
    {
        static::ensureKernelShutdown();

        return static::createClient(['debug' => false], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken,
        ]);
    }

    private function saveRevokedAccessToken(string $token): void
    {
        $query = <<<SQL
            INSERT INTO akeneo_connectivity_revoked_app_token (`token`)
            VALUES (:token)
            SQL;

        $this->connection->executeQuery($query, [
            'token' => $token,
        ]);
    }
}
