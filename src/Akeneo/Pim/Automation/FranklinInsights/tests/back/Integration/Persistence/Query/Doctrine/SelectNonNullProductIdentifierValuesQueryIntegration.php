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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Integration\Persistence\Query\Doctrine;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SelectNonNullProductIdentifierValuesQueryIntegration extends TestCase
{
    public function test_that_it_selects_non_null_requested_identifiers(): void
    {
        $this->insertSubscription(42, ['asin' => 'ABC123', 'upc' => '123456', 'brand' => null, 'mpn' => null]);
        $this->insertSubscription(56, ['asin' => null, 'upc' => null, 'brand' => 'Akeneo', 'mpn' => 'PIM']);

        $updatedIdentifiersSet = [
            ['asin'],
            ['upc'],
            ['asin', 'upc'],
        ];

        foreach ($updatedIdentifiersSet as $updatedIdentifiers) {
            Assert::assertSame(
                [
                    42 => ['asin' => 'ABC123', 'upc' => '123456'],
                ],
                $this->executeQuery($updatedIdentifiers, 0, 10)
            );
        }
    }

    public function test_that_it_does_not_select_null_requested_identifiers(): void
    {
        $this->insertSubscription(42, ['asin' => 'ABC123', 'upc' => null, 'brand' => null, 'mpn' => null]);

        $updatedIdentifiersSet = [
            ['upc'],
            ['brand'],
            ['mpn'],
            ['upc', 'brand', 'mpn'],
        ];

        foreach ($updatedIdentifiersSet as $updatedIdentifiers) {
            Assert::assertEmpty($this->executeQuery($updatedIdentifiers, 0, 10));
        }
    }

    public function test_that_it_can_search_after_a_product_id(): void
    {
        $this->insertSubscription(42, ['asin' => 'ABC123', 'upc' => null, 'brand' => null, 'mpn' => null]);
        $this->insertSubscription(10, ['asin' => 'DEF456', 'upc' => null, 'brand' => null, 'mpn' => null]);
        $this->insertSubscription(156, ['asin' => 'GHI789', 'upc' => null, 'brand' => null, 'mpn' => null]);
        $this->insertSubscription(30, ['asin' => 'AZE654', 'upc' => null, 'brand' => null, 'mpn' => null]);

        Assert::assertSame(
            [
                42 => ['asin' => 'ABC123'],
                156 => ['asin' => 'GHI789'],
            ],
            $this->executeQuery(['asin'], 30, 10)
        );
    }

    public function test_that_it_can_limit_the_number_of_results(): void
    {
        $this->insertSubscription(42, ['asin' => 'ABC123', 'upc' => '123456', 'brand' => null, 'mpn' => null]);
        $this->insertSubscription(10, ['asin' => 'DEF456', 'upc' => null, 'brand' => null, 'mpn' => null]);
        $this->insertSubscription(156, ['asin' => 'GHI789', 'upc' => null, 'brand' => null, 'mpn' => null]);
        $this->insertSubscription(30, ['asin' => 'AZE654', 'upc' => null, 'brand' => 'Akeneo', 'mpn' => 'pim']);

        Assert::assertSame([
            30 => ['asin' => 'AZE654', 'brand' => 'Akeneo', 'mpn' => 'pim'],
            42 => ['asin' => 'ABC123', 'upc' => '123456'],
        ], $this->executeQuery(['asin'], 15, 2));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param int $productId
     * @param array $requestedValues
     */
    private function insertSubscription(int $productId, array $requestedValues): void
    {
        $sql = <<<SQL
INSERT INTO pimee_franklin_insights_subscription(subscription_id, product_id, misses_mapping, requested_asin, requested_upc, requested_brand, requested_mpn)
VALUES (:subscriptionId, :productId, 0, :requestedAsin, :requestedUpc, :requestedBrand, :requestedMpn);
SQL;
        $this->getFromTestContainer('database_connection')->executeQuery($sql, [
            'subscriptionId' => uniqid(),
            'productId' => $productId,
            'requestedAsin' => $requestedValues['asin'] ?? null,
            'requestedUpc' => $requestedValues['upc'] ?? null,
            'requestedBrand' => $requestedValues['brand'] ?? null,
            'requestedMpn' => $requestedValues['mpn'] ?? null,
        ]);
    }

    /**
     * @param array $updatedIdentifiers
     * @param int $searchAfter
     * @param int $limit
     *
     * @return array
     */
    private function executeQuery(array $updatedIdentifiers, int $searchAfter, int $limit): array
    {
        return $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_non_null_requested_identifiers')
            ->execute($updatedIdentifiers, $searchAfter, $limit);
    }
}
