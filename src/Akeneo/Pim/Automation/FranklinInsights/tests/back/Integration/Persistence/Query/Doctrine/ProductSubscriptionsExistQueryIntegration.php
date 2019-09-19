<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSubscriptionsExistQueryIntegration extends TestCase
{
    private const SERVICE_NAME = 'akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.product_subscriptions_exist';

    public function testExecuteQueryWithProductIds()
    {
        $this->assertEquals([], $this->getFromTestContainer(self::SERVICE_NAME)->execute([]));

        $product1 = $this->createProduct('some_sku');
        $product2 = $this->createProduct('some_sku2');

        $this->assertEquals(
            [$product1->getId() => false, $product2->getId() => false],
            $this->getFromTestContainer(self::SERVICE_NAME)->execute([$product1->getId(), $product2->getId()])
        );

        $this->insertSubscription($product1->getId());
        $this->assertEquals(
            [$product1->getId() => true, $product2->getId() => false],
            $this->getFromTestContainer(self::SERVICE_NAME)->execute([$product1->getId(), $product2->getId()])
        );
    }

    public function testExecuteQueryWithProductIdentifiers()
    {
        $product1 = $this->createProduct('some_sku3');
        $this->createProduct('some_sku4');

        $this->assertEquals(
            ['some_sku3' => false, 'some_sku4' => false],
            $this->getFromTestContainer(self::SERVICE_NAME)->executeWithIdentifiers(['some_sku3', 'some_sku4'])
        );

        $this->insertSubscription($product1->getId());
        $this->assertEquals(
            ['some_sku3' => true, 'some_sku4' => false],
            $this->getFromTestContainer(self::SERVICE_NAME)->executeWithIdentifiers(['some_sku3', 'some_sku4'])
        );
    }

    /**
     * @param int $productId
     */
    private function insertSubscription(int $productId): void
    {
        $sql = <<<SQL
INSERT INTO pimee_franklin_insights_subscription(subscription_id, product_id, misses_mapping, requested_asin, requested_upc, requested_brand, requested_mpn)
VALUES (:subscriptionId, :productId, 0, null, null, null, null);
SQL;
        $this->getFromTestContainer('database_connection')->executeQuery($sql, [
            'subscriptionId' => uniqid(),
            'productId' => $productId,
        ]);
    }

    /**
     * @param string $identifier
     * @return ProductInterface
     */
    private function createProduct(string $identifier): ProductInterface
    {
        $product = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->build();
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
