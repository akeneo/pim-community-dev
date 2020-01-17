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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductAxisRateRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class ProductAxisRateRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var ProductAxisRateRepositoryInterface */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->repository = $this->get(ProductAxisRateRepository::class);
    }

    public function test_it_saves_multiple_product_rates_by_axis()
    {
        $productAxisRates = $this->findAllProductAxisRates();
        $this->assertEmpty($productAxisRates);

        $consistencyRates = [
            'ecommerce' => [
                'en_US' => 'B',
                'fr_FR' => 'E',
            ],
            'print' => [
                'en_US' => 'A',
                'fr_FR' => 'D',
            ],
        ];
        $enrichmentRates = [
            'ecommerce' => [
                'en_US' => 'D',
                'fr_FR' => 'A',
            ],
            'print' => [
                'en_US' => 'C',
                'fr_FR' => 'B',
            ],
        ];
        $this->repository->save([
            [
                'evaluated_at' => new \DateTimeImmutable(),
                'product_id' => new ProductId(123),
                'axis' => 'consistency',
                'rates' => $consistencyRates,
            ],
            [
                'evaluated_at' => new \DateTimeImmutable(),
                'product_id' => new ProductId(456),
                'axis' => 'enrichment',
                'rates' => $enrichmentRates,
            ]
        ]);

        $productAxisRates = $this->findAllProductAxisRates();

        $this->assertCount(2, $productAxisRates);
        $this->assertSame(123, (int) $productAxisRates[0]['product_id']);
        $this->assertEqualsCanonicalizing($consistencyRates, json_decode($productAxisRates[0]['rates'], true));
        $this->assertSame(456, (int) $productAxisRates[1]['product_id']);
        $this->assertEqualsCanonicalizing($enrichmentRates, json_decode($productAxisRates[1]['rates'], true));

        $consistencyRates = [
            'ecommerce' => [
                'en_US' => 'D',
                'fr_FR' => 'A',
            ],
            'print' => [
                'en_US' => 'E',
                'fr_FR' => 'E',
            ],
        ];
        $this->repository->save([
            [
                'evaluated_at' => new \DateTimeImmutable(),
                'product_id' => new ProductId(123),
                'axis' => 'consistency',
                'rates' => $consistencyRates,
            ]
        ]);

        $productAxisRates = $this->findAllProductAxisRates();

        $this->assertCount(2, $productAxisRates);
        $this->assertSame(123, (int) $productAxisRates[0]['product_id']);
        $this->assertEqualsCanonicalizing($consistencyRates, json_decode($productAxisRates[0]['rates'], true));
        $this->assertSame(456, (int) $productAxisRates[1]['product_id']);
        $this->assertEqualsCanonicalizing($enrichmentRates, json_decode($productAxisRates[1]['rates'], true));
    }

    public function test_it_purges_product_axis_rates_older_than_a_given_date()
    {
        $productAxisRates = [
            'product_123_consistency_last_rates' => [
                'evaluated_at' => new \DateTimeImmutable('2019-12-17'),
                'product_id' => new ProductId(123),
                'axis' => 'consistency',
                'rates' => [],
            ],
            'product_123_consistency_young_rates' => [
                'evaluated_at' => new \DateTimeImmutable('2019-12-16'),
                'product_id' => new ProductId(123),
                'axis' => 'consistency',
                'rates' => [],
            ],
            'product_123_consistency_old_rates' => [
                'evaluated_at' => new \DateTimeImmutable('2019-12-15'),
                'product_id' => new ProductId(123),
                'axis' => 'consistency',
                'rates' => [],
            ],
            'product_123_enrichment_old_but_last_rates' => [
                'evaluated_at' => new \DateTimeImmutable('2019-11-23'),
                'product_id' => new ProductId(123),
                'axis' => 'enrichment',
                'rates' => [],
            ],
            'product_42_consistency_last_rates' => [
                'evaluated_at' => new \DateTimeImmutable('2019-11-28'),
                'product_id' => new ProductId(42),
                'axis' => 'enrichment',
                'rates' => [],
            ],
            'product_42_consistency_old_rates' => [
                'evaluated_at' => new \DateTimeImmutable('2019-11-27'),
                'product_id' => new ProductId(123),
                'axis' => 'consistency',
                'rates' => [],
            ],
        ];

        $this->repository->save(array_values($productAxisRates));
        $this->assertCountProductAxisRates(6);

        $this->repository->purgeUntil(new \DateTimeImmutable('2019-12-16'));
        $this->assertCountProductAxisRates(4);

        $this->assertProductAxisRatesExists($productAxisRates['product_123_consistency_last_rates']);
        $this->assertProductAxisRatesExists($productAxisRates['product_123_consistency_young_rates']);
        $this->assertProductAxisRatesExists($productAxisRates['product_123_enrichment_old_but_last_rates']);
        $this->assertProductAxisRatesExists($productAxisRates['product_42_consistency_last_rates']);
    }

    private function findAllProductAxisRates(): array
    {
        $stmt = $this->db->query('SELECT * FROM pimee_data_quality_insights_product_axis_rates ORDER BY product_id');

        return $stmt->fetchAll();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertCountProductAxisRates(int $expectedCount): void
    {
        $stmt = $this->db->executeQuery(
            'SELECT COUNT(*) FROM pimee_data_quality_insights_product_axis_rates'
        );
        $count = intval($stmt->fetchColumn());

        $this->assertSame($expectedCount, $count);
    }

    private function assertProductAxisRatesExists(array $productAxisRates): void
    {
        $query = <<<SQL
SELECT 1 FROM pimee_data_quality_insights_product_axis_rates 
WHERE product_id = :product_id
    AND axis_code = :axis_code
    AND evaluated_at = :evaluated_at
SQL;

        $stmt = $this->db->executeQuery(
            $query,
            [
                'product_id' => $productAxisRates['product_id']->toInt(),
                'axis_code' => $productAxisRates['axis'],
                'evaluated_at' => $productAxisRates['evaluated_at']->format('Y-m-d')
            ]
        );

        $this->assertTrue((bool) $stmt->fetchColumn());
    }
}
