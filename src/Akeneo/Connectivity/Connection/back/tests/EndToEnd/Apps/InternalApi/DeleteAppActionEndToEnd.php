<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\InternalApi;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DeleteAppActionEndToEnd extends WebTestCase
{
    private FakeFeatureFlag $featureFlagMarketplaceActivate;
    private ConnectionLoader $connectionLoader;
    private ConnectedAppLoader $connectedAppLoader;
    private UserGroupLoader $userGroupLoader;

    public function test_to_successfully_reach_the_endpoint(): void
    {
        $this->connectionLoader->createConnection('magento', 'Magento connection', FlowType::DATA_DESTINATION, false);
        $this->userGroupLoader->create(['name' => 'app_7891011ghijkl']);
        $this->connectedAppLoader->createConnectedApp(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'Magento App',
            ['read_catalog_structure', 'read_products'],
            'magento',
            'http://www.magento.test/path/to/logo/b',
            'Magento Corp.',
            'app_7891011ghijkl',
            ['ecommerce'],
            true,
            null
        );

        $this->connectionLoader->createConnection('akeneo_print', 'Akeneo Print connection', FlowType::DATA_DESTINATION, false);
        $this->userGroupLoader->create(['name' => 'app_123456abcdef']);
        $this->connectedAppLoader->createConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'Akeneo Print app',
            ['read_catalog_structure', 'read_products'],
            'akeneo_print',
            'http://www.print.test/path/to/logo/a',
            'author',
            'app_123456abcdef',
            ['print'],
            false,
            'partner'
        );

        $this->client->request(
            'DELETE',
            '/rest/apps/2677e764-f852-4956-bf9b-1a1ec1b0d145',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $this->featureFlagMarketplaceActivate->disable();

        $this->client->request(
            'DELETE',
            '/rest/apps/2677e764-f852-4956-bf9b-1a1ec1b0d145',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $this->client->request(
            'DELETE',
            '/rest/apps/2677e764-f852-4956-bf9b-1a1ec1b0d145'
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        assert($response instanceof RedirectResponse);
        Assert::assertEquals('/', $response->getTargetUrl());
    }

    public function test_it_throws_access_denied_exception_with_missing_acl(): void
    {
        $this->removeAclFromRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');

        $this->client->request(
            'DELETE',
            '/rest/apps/2677e764-f852-4956-bf9b-1a1ec1b0d145',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlagMarketplaceActivate = $this->get('akeneo_connectivity.connection.marketplace_activate.feature');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');

        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->featureFlagMarketplaceActivate->enable();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
