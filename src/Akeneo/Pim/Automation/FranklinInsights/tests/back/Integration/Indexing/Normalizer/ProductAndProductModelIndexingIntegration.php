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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Proposal;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ProductAndProductModelIndexingIntegration extends TestCase
{
    public function test_normalizing_product_with_franklin_subscription(): void
    {
        $serializer = $this->getFromTestContainer('pim_indexing_serializer');

        $product = $this->getFromTestContainer('pim_catalog.repository.product')->findOneByIdentifier('bar');

        $serializedProduct = $serializer->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX);
        $this->assertArrayHasKey('franklin_subscription', $serializedProduct);
        $this->assertFalse($serializedProduct['franklin_subscription']);

        $this->insertSubscription(47);

        $serializedProduct = $serializer->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX);
        $this->assertArrayHasKey('franklin_subscription', $serializedProduct);
        $this->assertTrue($serializedProduct['franklin_subscription']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
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
}
