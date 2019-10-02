<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AddFranklinSubscriptionIntoElasticsearchProductProjectionIntegration extends TestCase
{
    public function test_that_it_adds_franklin_subscription_key_into_the_elasticsearch_projection_of_a_product(): void
    {
        $getElasticsearchProductProjection = $this->getFromTestContainer(
            'akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection'
        );

        $product = $this->createProduct('bar');
        $serializedProduct = $getElasticsearchProductProjection->fromProductIdentifiers(['bar'])['bar']->toArray();

        $this->assertArrayHasKey('franklin_subscription', $serializedProduct);
        $this->assertFalse($serializedProduct['franklin_subscription']);

        $this->insertSubscription($product->getId());

        $serializedProduct = $getElasticsearchProductProjection->fromProductIdentifiers(['bar'])['bar']->toArray();
        $this->assertArrayHasKey('franklin_subscription', $serializedProduct);
        $this->assertTrue($serializedProduct['franklin_subscription']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

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

    private function createProduct(string $identifier): Product
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.saver.product')->save($product);
        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $errors);

        return $product;
    }
}
