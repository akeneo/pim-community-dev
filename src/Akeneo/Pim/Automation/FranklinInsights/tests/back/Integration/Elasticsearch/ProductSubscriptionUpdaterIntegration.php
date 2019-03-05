<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;

class ProductSubscriptionUpdaterIntegration extends TestCase
{
    public function test_product_subscription_is_updated()
    {
        $family = $this->createFamily('family');
        $subscribedProduct = $this->createProduct('subscribed_product', $family->getCode());
        $unsubscribedProduct = $this->createProduct('unsubscribed_product', $family->getCode());
        $this->get('pim_catalog.elasticsearch.indexer.product')->indexAll([$subscribedProduct, $unsubscribedProduct]);

        $this->insertSubscription($subscribedProduct->getId());
        $productSubscriptionUpdater = $this->get('akeneo.pim.automation.franklin_insights.elasticsearch.updater.product_subscription');
        $productSubscriptionUpdater->updateSubscribedProduct($subscribedProduct->getId());
        $productSubscriptionUpdater->updateUnsubscribedProduct($unsubscribedProduct->getId());

        $this->assertProductIndexHasBeenUpdated($subscribedProduct, true);
        $this->assertProductIndexHasBeenUpdated($unsubscribedProduct, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createProduct(string $identifier, string $familyCode): ProductInterface
    {
        $product = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->withFamily($familyCode)
            ->withCategories('master')
            ->build();
        $violations = $this->getFromTestContainer('validator')->validate($product);
        Assert::assertSame(0, $violations->count(), sprintf('Product "%s" is not valid.', $identifier));
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function insertSubscription(int $productId): void
    {
        $query = <<<SQL
INSERT INTO pimee_franklin_insights_subscription (product_id, subscription_id, misses_mapping) 
VALUES (:productId, :subscriptionId, 1)
SQL;
        $queryParameters = [
            'productId'        => $productId,
            'subscriptionId'   => uniqid(),
        ];
        $types = [
            'productId'        => Type::INTEGER,
            'subscriptionId'   => Type::STRING,
        ];
        $this->get('doctrine.orm.entity_manager')->getConnection()->executeUpdate($query, $queryParameters, $types);
    }

    private function createFamily(string $familyCode): FamilyInterface
    {
        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build(['code' => $familyCode, 'attributes' => ['sku']]);
        $violations = $this->getFromTestContainer('validator')->validate($family);
        Assert::assertSame(0, $violations->count(), 'Family is not valid.');
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);

        return $family;
    }

    private function assertProductIndexHasBeenUpdated(ProductInterface $product, bool $isSubscribed): void
    {
        $esClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $esClient->refreshIndex();

        $searchResult = $esClient->search('pim_catalog_product', [
            'query'  => [
                'term' => [
                    'id' => sprintf('product_%d', $product->getId())
                ]
            ]
        ]);

        $productDocument = $searchResult['hits']['hits'][0]['_source'];
        Assert::assertArrayHasKey('franklin_subscription', $productDocument);
        Assert::assertSame($isSubscribed, $productDocument['franklin_subscription']);
    }
}
