<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllTestAppsActionEndToEnd extends WebTestCase
{
    protected function getConfiguration(): ?Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_all_test_app(): void
    {
        $this->get('akeneo_connectivity.connection.app_developer_mode.feature')->enable();

        $adminUser = $this->authenticateAsAdmin();
        $connection = $this->get('database_connection');
        $connection->insert('akeneo_connectivity_test_app', [
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => $adminUser->getId(),
        ]);

        $this->client->request(
            'GET',
            '/rest/marketplace/test-apps',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $result = \json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals([
            'total' => 1,
            'apps' => [
                [
                    'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                    'name' => 'My test app',
                    'logo' => null,
                    'author' => 'John Doe',
                    'partner' => null,
                    'description' => null,
                    'url' => null,
                    'categories' => [],
                    'certified' => false,
                    'activate_url' => 'http://shopware.example.com/activate?pim_url=http%3A%2F%2Flocalhost%3A8080',
                    'callback_url' => 'http://shopware.example.com/callback',
                    'connected' => false,
                    'isPending' => false,
                    'isTestApp' => true,
                ]
            ]
        ], $result);
    }
}
