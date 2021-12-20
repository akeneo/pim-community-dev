<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\CountAllConnectionsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountAllConnectionsQueryIntegration extends TestCase
{
    private CountAllConnectionsQuery $countAllConnectionsQuery;
    private ConnectionLoader $connectionLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->countAllConnectionsQuery = $this->get(CountAllConnectionsQuery::class);
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    public function test_it_returns_zero_when_there_is_no_connnetions()
    {
        $result = $this->countAllConnectionsQuery->execute();

        Assert::assertEquals(0, $result);
    }

    public function test_it_returns_connection_count()
    {
        $this->connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('translation', 'Translation', FlowType::OTHER, true);
        $this->connectionLoader->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION, false, 'app');
        $this->connectionLoader->createConnection('other', 'other', FlowType::DATA_DESTINATION, false, 'other');

        $result = $this->countAllConnectionsQuery->execute();

        Assert::assertEquals(4, $result);
    }
}
