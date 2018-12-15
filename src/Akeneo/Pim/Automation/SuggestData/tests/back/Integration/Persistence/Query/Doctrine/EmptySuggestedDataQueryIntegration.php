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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class EmptySuggestedDataQueryIntegration extends TestCase
{
    public function test_that_it_updates_all_suggested_data(): void
    {
        $product1 = $this->createProduct('my_product_1');
        $this->insertSubscription($product1->getId(), false);

        $product2 = $this->createProduct('my_product_2');
        $this->insertSubscription($product2->getId(), true);

        $this->createProduct('my_product_3');

        $this
            ->get('akeneo.pim.automation.suggest_data.infrastructure.persistence.query.empty_suggested_data_query')
            ->execute();

        $this->assertAllSubscriptionsAreEmpty();
    }

    /**
     * @return \Akeneo\Test\Integration\Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * Asserts that all the subscriptions are empty.
     */
    private function assertAllSubscriptionsAreEmpty(): void
    {
        $query = <<<SQL
SELECT *
FROM pim_suggest_data_product_subscription
WHERE raw_suggested_data IS NOT NULL
SQL;

        $connection = $this->get('doctrine.orm.entity_manager')->getConnection();
        $this->assertEquals(0, $connection->executeQuery($query)->rowCount());
    }

    /**
     * @param int $productId
     * @param bool $isMappingMissing
     */
    private function insertSubscription(int $productId, bool $isMappingMissing): void
    {
        $query = <<<SQL
INSERT INTO pim_suggest_data_product_subscription (subscription_id, product_id, raw_suggested_data, misses_mapping) 
VALUES (:subscriptionId, :productId, :rawSuggestedData, :isMappingMissing)
SQL;

        $queryParameters = [
            'subscriptionId' => uniqid(),
            'productId' => $productId,
            'rawSuggestedData' => '{}',
            'isMappingMissing' => $isMappingMissing,
        ];
        $parametersTypes = [
            'subscriptionId' => Type::STRING,
            'productId' => Type::INTEGER,
            'rawSuggestedData' => Type::STRING,
            'isMappingMissing' => Type::BOOLEAN,
        ];

        $this
            ->get('doctrine.orm.entity_manager')
            ->getConnection()
            ->executeUpdate($query, $queryParameters, $parametersTypes);
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
}
