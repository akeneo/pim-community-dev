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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Repository\Doctrine;

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
    public function test_it_saves_a_product_subscription()
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = 'a-random-string';
        $subscription = new ProductSubscription($product, $subscriptionId);
        $subscription->setSuggestedData(new SuggestedData(['foo' => 'bar']));

        $this->getRepository()->save($subscription);

        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT product_id, subscription_id, raw_suggested_data from pim_suggest_data_product_subscription;'
        );
        $retrievedSubscriptions = $statement->fetchAll();

        Assert::assertCount(1, $retrievedSubscriptions);
        Assert::assertEquals(
            [
                'product_id'      => $product->getId(),
                'subscription_id' => $subscriptionId,
                'raw_suggested_data'  => '{"foo": "bar"}',
            ],
            $retrievedSubscriptions[0]
        );
    }

    public function test_it_finds_a_subscription_by_product_and_subscription_id()
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = uniqid();
        $suggestedData = [
            'an_attribute'      => 'some data',
            'another_attribute' => 'some other data',
        ];
        $this->insertSuggestedData($product->getId(), $subscriptionId, $suggestedData);

        $subscription = $this->getRepository()->findOneByProductAndSubscriptionId($product, $subscriptionId);
        Assert::assertInstanceOf(ProductSubscription::class, $subscription);
        Assert::assertSame($product, $subscription->getProduct());
        Assert::assertSame($subscriptionId, $subscription->getSubscriptionId());
        Assert::assertSame($suggestedData, $subscription->getSuggestedData()->getValues());
    }

    public function test_that_it_gets_a_subscription_from_a_product_id()
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = uniqid();
        $suggestedData = [
            'an_attribute'      => 'some data',
            'another_attribute' => 'some other data',
        ];
        $this->insertSuggestedData($product->getId(), $subscriptionId, $suggestedData);

        $subscription = $this->getRepository()->findOneByProductId($product->getId());
        Assert::assertInstanceOf(ProductSubscription::class, $subscription);
        Assert::assertSame($subscriptionId, $subscription->getSubscriptionId());
        Assert::assertSame($suggestedData, $subscription->getSuggestedData()->getValues());
    }

    public function test_that_it_gets_null_for_a_non_subscribed_product_id()
    {
        $result = $this->getRepository()->findOneByProductId(42);

        Assert::assertTrue(null === $result);
    }

    public function test_it_saves_empty_suggested_data_as_null()
    {
        $subscription1 = new ProductSubscription($this->createProduct('a_product'), 'subscription-1');
        $subscription1->setSuggestedData(new SuggestedData(null));
        $this->getRepository()->save($subscription1);

        $subscription2 = new ProductSubscription($this->createProduct('another_product'), 'subscription-2');
        $this->getRepository()->save($subscription2);

        $subscription3 = new ProductSubscription($this->createProduct('a_third_product'), 'subscription-3');
        $subscription3->setSuggestedData(new SuggestedData([]));
        $this->getRepository()->save($subscription3);

        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT raw_suggested_data from pim_suggest_data_product_subscription;'
        );

        $subscriptionRows = $statement->fetchAll();

        Assert::assertCount(3, $subscriptionRows);

        foreach ($subscriptionRows as $subscriptionRow) {
            Assert::isNull($subscriptionRow['raw_suggested_data']);
        }
    }

    public function test_it_fetches_a_null_raw_suggested_data_as_empty_array()
    {
        $product = $this->createProduct('a_product');

        $query = <<<SQL
INSERT INTO pim_suggest_data_product_subscription (product_id, subscription_id, raw_suggested_data) 
VALUES (:productId, :subscriptionId, :suggestedData)
SQL;
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->prepare($query);
        $statement->execute(
            [
                'productId'      => $product->getId(),
                'subscriptionId' => uniqid(),
                'suggestedData'  => null,
            ]
        );

        $subscription = $this->getRepository()->findOneByProductId($product->getId());
        Assert::assertInstanceOf(ProductSubscription::class, $subscription);
        Assert::assertInstanceOf(SuggestedData::class, $subscription->getSuggestedData());
        Assert::assertTrue($subscription->getSuggestedData()->isEmpty());
    }

    public function test_it_finds_pending_product_subscriptions()
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = uniqid();
        $suggestedData = [
            'an_attribute' => 'some data',
            'another_attribute' => 'some other data',
        ];
        $this->insertSuggestedData($product->getId(), $subscriptionId, $suggestedData);

        $otherProduct = $this->createProduct('another_product');
        $otherSubscriptionId = uniqid();
        $this->insertSuggestedData($otherProduct->getId(), $otherSubscriptionId, []);

        $pendingSubscriptions = $this->getRepository()->findPendingSubscriptions();
        Assert::assertCount(1, $pendingSubscriptions);
        Assert::assertSame($product, $pendingSubscriptions[0]->getProduct());
        Assert::assertSame($subscriptionId, $pendingSubscriptions[0]->getSubscriptionId());
        Assert::assertSame($suggestedData, $pendingSubscriptions[0]->getSuggestedData()->getValues());
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
    private function insertSuggestedData(int $productId, string $subscriptionId, array $suggestedData): void
    {
        $query = <<<SQL
INSERT INTO pim_suggest_data_product_subscription (product_id, subscription_id, raw_suggested_data) 
VALUES (:productId, :subscriptionId, :suggestedData)
SQL;
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->prepare($query);
        $statement->execute(
            [
                'productId' => $productId,
                'subscriptionId' => $subscriptionId,
                'suggestedData' => empty($suggestedData) ? null: json_encode($suggestedData),
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
