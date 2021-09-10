<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DbalConnectedAppRepository;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalConnectedAppRepositoryIntegration extends TestCase
{
    /** @var DbalConnectedAppRepository */
    private $repository;

    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var ConnectedAppLoader */
    private $connectedAppLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_connectivity.connection.persistence.repository.connected_app');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_finds_all_ordered_by_name()
    {
        $this->connectionLoader->createConnection('connectionCodeB', 'Connector B', FlowType::DATA_DESTINATION, false);
        $this->connectedAppLoader->createConnectedApp(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'App B',
            ['scope B1', 'scope B2'],
            'connectionCodeB',
            'http://www.example.com/path/to/logo/b',
            'author B',
            ['category B1'],
            true,
            null,
            null
        );

        $this->connectionLoader->createConnection('connectionCodeA', 'Connector A', FlowType::DATA_DESTINATION, false);
        $this->connectedAppLoader->createConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'App A',
            ['scope A1'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            ['category A1', 'category A2'],
            false,
            'partner A',
            'http://www.example.com/path/to/app/a'
        );

        $connectedApps = $this->repository->findAll();

        Assert::assertInstanceOf(ConnectedApp::class, $connectedApps[0]);
        Assert::assertSame('0dfce574-2238-4b13-b8cc-8d257ce7645b', $connectedApps[0]->getId());
        Assert::assertSame('App A', $connectedApps[0]->getName());
        Assert::assertIsArray($connectedApps[0]->getScopes());
        Assert::assertCount(1, $connectedApps[0]->getScopes());
        Assert::assertSame('scope A1', $connectedApps[0]->getScopes()[0]);
        Assert::assertSame('connectionCodeA', $connectedApps[0]->getConnectionCode());
        Assert::assertSame('author A', $connectedApps[0]->getAuthor());
        Assert::assertIsArray($connectedApps[0]->getCategories());
        Assert::assertCount(2, $connectedApps[0]->getCategories());
        Assert::assertSame('category A1', $connectedApps[0]->getCategories()[0]);
        Assert::assertSame('category A2', $connectedApps[0]->getCategories()[1]);
        Assert::assertFalse($connectedApps[0]->isCertified());
        Assert::assertSame('partner A', $connectedApps[0]->getPartner());
        Assert::assertSame('http://www.example.com/path/to/app/a', $connectedApps[0]->getExternalUrl());

        Assert::assertInstanceOf(ConnectedApp::class, $connectedApps[1]);
        Assert::assertSame('App B', $connectedApps[1]->getName());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
