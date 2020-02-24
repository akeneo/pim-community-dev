<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\ReadProducts;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\ReadProductRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

class DbalReadProductRepositoryIntegration extends TestCase
{
    public function test_it_creates_read_product_events(): void
    {
        $readProducts = new ReadProducts(
            'ecommerce',
            [4, 2, 6],
            new DateTimeImmutable('2020-02-24 10:07:32', new \DateTimeZone('UTC'))
        );

        $this->getReadProductsRepository()->bulkInsert($readProducts);

        $sql = <<<SQL
SELECT product_id, connection_code, event_datetime
FROM akeneo_connectivity_connection_audit_read_product
ORDER BY product_id
SQL;
        $result = $this->getDbalConnection()->fetchAll($sql);

        Assert::assertCount(3, $result);
        Assert::assertEquals(
            [
                ['connection_code' => 'ecommerce', 'event_datetime' => '2020-02-24 10:07:32', 'product_id' => 2],
                ['connection_code' => 'ecommerce', 'event_datetime' => '2020-02-24 10:07:32', 'product_id' => 4],
                ['connection_code' => 'ecommerce', 'event_datetime' => '2020-02-24 10:07:32', 'product_id' => 6]
            ],
            $result
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getReadProductsRepository(): ReadProductRepository
    {
        return $this->get('akeneo_connectivity.connection.persistence.repository.read_product');
    }

    private function getDbalConnection(): DbalConnection
    {
        return $this->get('database_connection');
    }
}
