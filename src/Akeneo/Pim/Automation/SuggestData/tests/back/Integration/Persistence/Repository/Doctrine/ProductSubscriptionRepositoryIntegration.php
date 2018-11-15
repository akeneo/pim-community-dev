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

namespace Akeneo\Test\Pim\Automation\SuggestData\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRepositoryIntegration extends TestCase
{
    public function test_it_saves_a_product_subscription(): void
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = 'a-random-string';
        $subscription = new ProductSubscription(
            $product,
            $subscriptionId,
            ['upc' => '72527273070', 'asin' => 'B00005N5PF', 'mpn' => 'AS4561AD142', 'brand' => 'intel']
        );
        $subscription->setSuggestedData(new SuggestedData([['pimAttributeCode' => 'foo', 'value' => 'bar']]));

        $this->getRepository()->save($subscription);

        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            '
            SELECT product_id, subscription_id, raw_suggested_data, 
            requested_upc, requested_asin, requested_mpn, requested_brand 
            from pim_suggest_data_product_subscription;
        '
        );
        $retrievedSubscriptions = $statement->fetchAll();

        Assert::assertCount(1, $retrievedSubscriptions);
        $subscriptionData = $retrievedSubscriptions[0];
        $expectedValues = [
            'product_id' => $product->getId(),
            'subscription_id' => $subscriptionId,
            'requested_upc' => '72527273070',
            'requested_asin' => 'B00005N5PF',
            'requested_mpn' => 'AS4561AD142',
            'requested_brand' => 'intel',
        ];

        foreach ($expectedValues as $key => $expected) {
            Assert::assertEquals($expected, $subscriptionData[$key]);
        }
        Assert::assertEquals(
            [['pimAttributeCode' => 'foo', 'value' => 'bar']],
            json_decode($subscriptionData['raw_suggested_data'], true)
        );
    }

    public function test_that_it_gets_a_subscription_from_a_product_id(): void
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = uniqid();
        $suggestedData = [
            ['pimAttributeCode' => 'an_attribute', 'value' => 'some data'],
            ['pimAttributeCode' => 'another_attribute', 'value' => 'some other data'],
        ];
        $this->insertSubscription($product->getId(), $subscriptionId, $suggestedData);

        $subscription = $this->getRepository()->findOneByProductId($product->getId());
        Assert::assertInstanceOf(ProductSubscription::class, $subscription);
        Assert::assertSame($subscriptionId, $subscription->getSubscriptionId());
        Assert::assertSame($suggestedData, $subscription->getSuggestedData()->getRawValues());
    }

    public function test_that_it_gets_null_for_a_non_subscribed_product_id(): void
    {
        $result = $this->getRepository()->findOneByProductId(42);

        Assert::assertTrue(null === $result);
    }

    public function test_it_saves_empty_suggested_data_as_null(): void
    {
        $subscription1 = new ProductSubscription(
            $this->createProduct('a_product'),
            'subscription-1',
            ['sku' => '72527273070']
        );
        $subscription1->setSuggestedData(new SuggestedData([]));
        $this->getRepository()->save($subscription1);

        $subscription2 = new ProductSubscription(
            $this->createProduct(
                'another_product'
            ),
            'subscription-2',
            ['sku' => '72527273070']
        );
        $this->getRepository()->save($subscription2);

        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT raw_suggested_data from pim_suggest_data_product_subscription;'
        );

        $subscriptionRows = $statement->fetchAll();

        Assert::assertCount(2, $subscriptionRows);

        foreach ($subscriptionRows as $subscriptionRow) {
            Assert::isNull($subscriptionRow['raw_suggested_data']);
        }
    }

    public function test_it_fetches_a_null_raw_suggested_data_as_empty_array(): void
    {
        $product = $this->createProduct('a_product');
        $this->insertSubscription($product->getId(), uniqid(), []);

        $subscription = $this->getRepository()->findOneByProductId($product->getId());
        Assert::assertInstanceOf(ProductSubscription::class, $subscription);
        Assert::assertInstanceOf(SuggestedData::class, $subscription->getSuggestedData());
        Assert::assertTrue($subscription->getSuggestedData()->isEmpty());
    }

    public function test_that_it_finds_and_paginates_pending_product_subscriptions(): void
    {
        $product1 = $this->createProduct('product_1');
        $this->insertSubscription($product1->getId(), 'c', [['pimAttributeCode' => 'foo', 'value' => 'bar']]);
        $product2 = $this->createProduct('product_2');
        $this->insertSubscription($product2->getId(), 'b', []);
        $product3 = $this->createProduct('product_3');
        $this->insertSubscription($product3->getId(), 'd', []);
        $product4 = $this->createProduct('product_4');
        $this->insertSubscription($product4->getId(), 'a', [['pimAttributeCode' => 'bar', 'value' => 'baz']]);
        $product5 = $this->createProduct('product_5');
        $this->insertSubscription($product5->getId(), 'e', [['pimAttributeCode' => 'baz', 'value' => '42']]);

        $params = [
            [10, null, ['a', 'c', 'e']],
            [2, null, ['a', 'c']],
            [1, 'b', ['c']],
            [2, 'a', ['c', 'e']],
            [10, 'd', ['e']],
            [10, 'e', []],
        ];

        foreach ($params as $param) {
            list($limit, $searchAfter, $expectedIds) = $param;
            $pendingSubscriptions = $this->getRepository()->findPendingSubscriptions($limit, $searchAfter);

            Assert::assertCount(count($expectedIds), $pendingSubscriptions);
            foreach ($pendingSubscriptions as $pendingSubscription) {
                Assert::assertContains($pendingSubscription->getSubscriptionId(), $expectedIds);
            }
        }
    }

    public function test_it_deletes_a_subscription(): void
    {
        $productId = $this->createProduct('a_product_to_delete')->getId();
        $subscriptionId = uniqid();
        $suggestedData = [
            ['pimAttributeCode' => 'an_attribute', 'value' => 'some data'],
            ['pimAttributeCode' => 'another_attribute', 'value' => 'some other data'],
        ];
        $this->insertSubscription($productId, $subscriptionId, $suggestedData);

        $subscription = $this->getRepository()->findOneByProductId($productId);
        Assert::assertInstanceOf(ProductSubscription::class, $subscription);

        $this->getRepository()->delete($subscription);

        $subscription = $this->getRepository()->findOneByProductId($productId);
        Assert::assertNull($subscription);
    }

    public function test_it_empties_suggested_data_for_specified_ids(): void
    {
        $product1 = $this->createProduct('product_1');
        $this->insertSubscription(
            $product1->getId(),
            'subscription_to_empty',
            [['pimAttributeCode' => 'foo', 'value' => 'bar']]
        );
        $product2 = $this->createProduct('product_2');
        $this->insertSubscription(
            $product2->getId(),
            'other-subscription',
            [['pimAttributeCode' => 'bar', 'value' => 'baz']]
        );

        $repo = $this->getRepository();
        $repo->emptySuggestedData([$product1->getId()]);

        Assert::assertTrue($repo->findOneByProductId($product1->getId())->getSuggestedData()->isEmpty());
        Assert::assertFalse($repo->findOneByProductId($product2->getId())->getSuggestedData()->isEmpty());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    private function createProduct(string $identifier): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('validator')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param int $productId
     * @param string $subscriptionId
     * @param array|null $suggestedData
     */
    private function insertSubscription(int $productId, string $subscriptionId, array $suggestedData): void
    {
        $query = <<<SQL
INSERT INTO pim_suggest_data_product_subscription (product_id, subscription_id, raw_suggested_data, misses_mapping) 
VALUES (:productId, :subscriptionId, :suggestedData, false)
SQL;
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->prepare($query);
        $statement->execute(
            [
                'productId' => $productId,
                'subscriptionId' => $subscriptionId,
                'suggestedData' => empty($suggestedData) ? null : json_encode($suggestedData),
            ]
        );
    }

    /**
     * @return ProductSubscriptionRepositoryInterface
     */
    private function getRepository(): ProductSubscriptionRepositoryInterface
    {
        return $this->get('akeneo.pim.automation.suggest_data.repository.product_subscription');
    }
}
