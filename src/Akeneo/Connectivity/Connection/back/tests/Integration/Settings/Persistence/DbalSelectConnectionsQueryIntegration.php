<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Settings\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\Persistence\DbalSelectConnectionsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsQueryIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private SelectConnectionsQueryInterface $selectConnectionsQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->selectConnectionsQuery = $this->get(DbalSelectConnectionsQuery::class);
    }

    public function test_it_fetches_connections(): void
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
        \sleep(1); // Avoid having the same creation datetime
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::OTHER, false);
        \sleep(1);
        $this->connectionLoader->createConnection('app', 'App', FlowType::OTHER, false, 'app');

        $connections = $this->selectConnectionsQuery->execute([ConnectionType::DEFAULT_TYPE]);

        Assert::assertCount(2, $connections);
        Assert::assertContainsOnlyInstancesOf(Connection::class, $connections);
        Assert::assertSame('magento', $connections[0]->code());
        Assert::assertSame('bynder', $connections[1]->code());

        $connections = $this->selectConnectionsQuery->execute([ConnectionType::APP_TYPE]);

        Assert::assertCount(1, $connections);
        Assert::assertSame('app', $connections[0]->code());
    }

    public function test_it_fetches_without_connection(): void
    {
        $connections = $this->selectConnectionsQuery->execute();

        Assert::assertCount(0, $connections);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
