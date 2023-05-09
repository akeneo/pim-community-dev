<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Settings\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\Service\GetConnectionsNumberLimit;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\Persistence\IsConnectionsNumberLimitReachedQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedQueryIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private IsConnectionsNumberLimitReachedQuery $connectionsNumberLimitReachedQuery;
    private GetConnectionsNumberLimit $getConnectionsNumberLimit;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->connectionsNumberLimitReachedQuery = $this->get(IsConnectionsNumberLimitReachedQuery::class);
        $this->getConnectionsNumberLimit = $this->get(GetConnectionsNumberLimit::class);

        $this->getConnectionsNumberLimit->setLimit(50);
    }

    public function test_it_returns_false_when_connection_count_is_below_the_limit(): void
    {
        $result = $this->connectionsNumberLimitReachedQuery->execute();

        Assert::assertFalse($result);

        $this->connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $result = $this->connectionsNumberLimitReachedQuery->execute();

        Assert::assertFalse($result);
    }

    public function test_it_returns_false_when_connection_count_is_above_the_limit(): void
    {
        $this->getConnectionsNumberLimit->setLimit(3);

        $this->connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('translation', 'Translation', FlowType::OTHER, true);
        $this->connectionLoader->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION, false, 'app');

        $result = $this->connectionsNumberLimitReachedQuery->execute();

        Assert::assertTrue($result);
    }
}
