<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAccessTokenQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetAccessTokenQueryIntegration extends TestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private GetAccessTokenQuery $query;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetAccessTokenQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_gets_the_access_token_in_terms_of_the_given_client_and_scopes(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'foo',
            ['read_products', 'write_products']
        );

        $token = $this->query->execute('2677e764-f852-4956-bf9b-1a1ec1b0d145', 'read_products write_products');
        Assert::assertNotNull($token);
        Assert::assertSame('foo', $token);
    }

    public function test_it_does_not_get_the_access_token_if_the_scopes_have_changed(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'foo',
            ['read_products']
        );

        $token = $this->query->execute('2677e764-f852-4956-bf9b-1a1ec1b0d145', 'read_products write_products');
        Assert::assertNull($token);
    }

    public function test_it_does_not_get_the_access_token_if_less_scopes_are_requested(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'foo',
            ['read_products', 'write_products']
        );

        $token = $this->query->execute('2677e764-f852-4956-bf9b-1a1ec1b0d145', 'read_products');
        Assert::assertNull($token);
    }

    public function test_it_does_not_get_the_access_token_if_it_does_not_exist(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'foo',
            ['read_products']
        );

        $token = $this->query->execute('123456-1234-abcd-abcd-1a1ec1b0d145', 'read_products');
        Assert::assertNull($token);
    }
}
