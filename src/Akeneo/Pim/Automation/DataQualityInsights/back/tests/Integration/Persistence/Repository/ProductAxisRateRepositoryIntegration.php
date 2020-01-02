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

    private function findAllProductAxisRates(): array
    {
        $stmt = $this->db->query('SELECT * FROM pimee_data_quality_insights_product_axis_rates ORDER BY product_id');

        return $stmt->fetchAll();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
