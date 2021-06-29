<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Analytics\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Platform\Bundle\AnalyticsBundle\Query\Sql\ApiConnectionCount;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ApiConnectionCountIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var ApiConnectionCount */
    private $apiConnectionCountQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->apiConnectionCountQuery = $this->get('pim_analytics.query.api_connection_count');
    }

    public function test_it_fetches_connection_count()
    {
        $this->connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('csv', 'CSV', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
        $this->connectionLoader->createConnection('priint', 'Priint Connector', FlowType::DATA_DESTINATION, true);
        $this->connectionLoader->createConnection('marketplace', 'Marketplace Connector', FlowType::DATA_DESTINATION, false);
        $this->connectionLoader->createConnection('translation', 'Translation tool', FlowType::OTHER, false);

        $result = $this->apiConnectionCountQuery->fetch();

        $expectedResult = [
            'data_source' => ['tracked' => 2, 'untracked' => 0],
            'data_destination' => ['tracked' => 1, 'untracked' => 2],
            'other' => ['tracked' => 0, 'untracked' => 1],
        ];

        Assert::assertEquals($expectedResult, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
