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

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Doctrine\EmptySuggestedDataAndMissingMappingQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class EmptySuggestedDataAndMissingMappingQueryIntegration extends TestCase
{
    public function test_that_it_updates_suggested_data_and_misses_mapping_for_a_specific_family(): void
    {
        $this->createFamily('my_family');
        $this->createFamily('camcorders');

        $product1 = $this->createProduct('my_product_1', 'my_family');
        $this->insertSubscription($product1->getId(), false);

        $product2 = $this->createProduct('my_product_2', 'camcorders');
        $this->insertSubscription($product2->getId(), true);

        $product3 = $this->createProduct('my_product_3', 'my_family');
        $this->insertSubscription($product3->getId(), true);

        $this->createProduct('my_product_4', 'my_family');

        $this
            ->getQueryService()
            ->execute('my_family');

        $this->assertNotEmptySubscriptions([$product2->getId()]);
        $this->assertEmptySubscriptions([$product1->getId(), $product3->getId()], [$product2->getId()]);
    }

    /**
     * @return \Akeneo\Test\Integration\Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return EmptySuggestedDataAndMissingMappingQuery
     */
    private function getQueryService(): EmptySuggestedDataAndMissingMappingQuery
    {
        return $this
            ->get('akeneo.pim.automation.suggest_data.infrastructure.persistence.query.empty_suggested_data_and_missing_mapping_query');
    }

    /**
     * Asserts the subscriptions that have not been updated.
     *
     * @param array $notEmptySubscriptions
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function assertNotEmptySubscriptions(array $notEmptySubscriptions): void
    {
        $queryNotEmpty = <<<SQL
SELECT product_id
FROM pim_suggest_data_product_subscription
WHERE raw_suggested_data IS NOT NULL
ORDER BY product_id
SQL;

        /** @var Connection $connection */
        $connection = $this->get('doctrine.orm.entity_manager')->getConnection();
        $resultNotEmptyRows = $connection->query($queryNotEmpty)->fetchAll();

        $this->assertCount(count($notEmptySubscriptions), $resultNotEmptyRows);
        foreach ($resultNotEmptyRows as $index => $resultNotEmptyRow) {
            $this->assertEquals($notEmptySubscriptions[$index], (int) $resultNotEmptyRow['product_id']);
        }
    }

    /**
     * Asserts the subscription that have been emptied.
     *
     * @param array $emptySubscriptions
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function assertEmptySubscriptions(array $emptySubscriptions): void
    {
        $queryEmpty = <<<SQL
SELECT product_id, misses_mapping
FROM pim_suggest_data_product_subscription
WHERE raw_suggested_data IS NULL
AND misses_mapping IS NULL
SQL;
        /** @var Connection $connection */
        $connection = $this->get('doctrine.orm.entity_manager')->getConnection();
        $resultEmptyRows = $connection->query($queryEmpty)->fetchAll();

        $this->assertCount(count($emptySubscriptions), $resultEmptyRows);
        foreach ($resultEmptyRows as $index => $resultEmptyRow) {
            $this->assertEquals($emptySubscriptions[$index], (int) $resultEmptyRow['product_id']);
        }
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
            ->getFromTestContainer('doctrine.orm.entity_manager')
            ->getConnection()
            ->executeUpdate($query, $queryParameters, $parametersTypes);
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    private function createProduct(string $identifier, string $familyCode): ProductInterface
    {
        $product = $this->getFromTestContainer('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->getFromTestContainer('validator')->validate($product);
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function createFamily(string $familyCode): void
    {
        $familyData = [
            'code' => $familyCode,
            'attributes' => ['sku'],
        ];

        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build($familyData);

        $this->getFromTestContainer('validator')->validate($family);

        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);
    }
}
