<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAppQueryIntegration extends TestCase
{
    private GetAppQuery $query;
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetAppQuery::class);
        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->connection = $this->get('database_connection');

        $this->loadAppsFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadAppsFixtures(): void
    {
        $apps = [
            [
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ],
        ];

        $this->webMarketplaceApi->setApps($apps);
    }

    public function test_it_returns_an_app(): void
    {
        $result = $this->query->execute('90741597-54c5-48a1-98da-a68e7ee0a715');

        $this->assertEquals(
            App::fromWebMarketplaceValues([
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ]),
            $result
        );
    }

    public function test_it_returns_null_when_app_does_not_exist(): void
    {
        $result = $this->query->execute('wrong_id');

        $this->assertNull($result);
    }

    public function test_it_returns_a_custom_app(): void
    {
        $user = $this->createAdminUser();
        $this->createCustomApp([
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => $user->getId(),
        ]);

        $result = $this->query->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');

        $this->assertEquals(
            App::fromCustomAppValues([
                'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                'secret' => 'foobar',
                'name' => 'My test app',
                'author' => 'John Doe',
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
                'connected' => false,
            ]),
            $result
        );
    }

    public function test_it_returns_null_when_custom_app_does_not_exist(): void
    {
        $result = $this->query->execute('wrong_id');

        $this->assertNull($result);
    }

    /**
     * @param array{
     *     client_id: string,
     *     client_secret: string,
     *     name: string,
     *     activate_url: string,
     *     callback_url: string,
     *     user_id: string|null,
     * } $data
     */
    private function createCustomApp(array $data): void
    {
        $this->connection->insert('akeneo_connectivity_test_app', $data);
    }
}
