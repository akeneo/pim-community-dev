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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Persistence\Query\Subscription;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class EmptySuggestedDataQueryIntegration extends TestCase
{
    public function test_that_it_empties_suggested_data_from_subscriptions(): void
    {
        $this->createSubscription('abc-def-123', ['bar' => 'baz']);
        $this->createSubscription('not-to-be-emptied', ['bar' => 'baz']);
        $this->createSubscription('654-zer-uio', ['test' => 42]);

        $this->get(
            'akeneo.pim.automation.suggest_data.infrastructure.persistence.query.subscription.empty_suggested_data'
        )->execute(['abc-def-123', '654-zer-uio']);

        $this->assertSuggestedData('abc-def-123', true);
        $this->assertSuggestedData('654-zer-uio', true);
        $this->assertSuggestedData('not-to-be-emptied', false);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param string $subscriptionId
     * @param array|null $suggestedData
     */
    private function createSubscription(string $subscriptionId, array $suggestedData): void
    {
        $product = $this->createProduct(uniqid());

        $query = <<<SQL
INSERT INTO pim_suggest_data_product_subscription (product_id, subscription_id, raw_suggested_data, misses_mapping) 
VALUES (:productId, :subscriptionId, :suggestedData, false)
SQL;
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->prepare($query);
        $statement->execute(
            [
                'productId' => $product->getId(),
                'subscriptionId' => $subscriptionId,
                'suggestedData' => empty($suggestedData) ? null : json_encode($suggestedData),
            ]
        );
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
     * @param string $subscriptionId
     * @param bool $isEmpty
     */
    private function assertSuggestedData(string $subscriptionId, bool $isEmpty): void
    {
        $sql = <<<SQL
SELECT raw_suggested_data from pim_suggest_data_product_subscription
WHERE subscription_id = :subscriptionId;
SQL;
        $statement = $this->get('doctrine.orm.entity_manager')->getConnection()->executeQuery(
            $sql,
            ['subscriptionId' => $subscriptionId]
        );
        $result = $statement->fetch();

        if (true === $isEmpty) {
            Assert::null($result['raw_suggested_data']);
        } else {
            Assert::notNull($result['raw_suggested_data']);
        }
    }
}
