<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

final class ElasticsearchCheckerIntegration extends TestCase
{
    public function test_elasticsearch_is_ok_when_all_pim_indexes_are_available(): void
    {
        Assert::assertEquals(ServiceStatus::ok(), $this->getElasticsearchChecker()->status());
    }

    public function test_elasticsearch_is_ko_when_one_of_the_indexes_is_not_available(): void
    {
        $indexName = $this->getParameter('product_and_product_model_index_name');
        $this->getProductAndProductModelClient()->deleteIndex();

        Assert::assertEquals(
            ServiceStatus::notOk('Elasticsearch failing indexes: '. $indexName),
            $this->getElasticsearchChecker()->status()
        );
    }

    protected function tearDown(): void
    {
        $this->getProductAndProductModelClient()->resetIndex();
        parent::tearDown();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getElasticsearchChecker(): ElasticsearchChecker
    {
        return $this->get('Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker');
    }

    private function getProductAndProductModelClient(): Client
    {
        return $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }
}
