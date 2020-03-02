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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
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

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $consistencyRates = (new ChannelLocaleRateCollection())
            ->addRate($channelEcommerce, $localeEn, new Rate(87))
            ->addRate($channelEcommerce, $localeFr, new Rate(47))
            ->addRate($channelPrint, $localeEn, new Rate(100))
            ->addRate($channelPrint, $localeFr, new Rate(67))
        ;
        $enrichmentRates = (new ChannelLocaleRateCollection())
            ->addRate($channelEcommerce, $localeEn, new Rate(65))
            ->addRate($channelEcommerce, $localeFr, new Rate(97))
            ->addRate($channelPrint, $localeEn, new Rate(76))
            ->addRate($channelPrint, $localeFr, new Rate(84))
        ;

        $expectedConsistencyRates = [
            'ecommerce' => [
                'en_US' => [
                    'rank' => 2,
                    'value' => 87
                ],
                'fr_FR' => [
                    'rank' => 5,
                    'value' => 47
                ],
            ],
            'print' => [
                'en_US' => [
                    'rank' => 1,
                    'value' => 100
                ],
                'fr_FR' => [
                    'rank' => 4,
                    'value' => 67
                ],
            ],
        ];
        $expectedEnrichmentRates = [
            'ecommerce' => [
                'en_US' => [
                    'rank' => 4,
                    'value' => 65
                ],
                'fr_FR' => [
                    'rank' => 1,
                    'value' => 97
                ],
            ],
            'print' => [
                'en_US' => [
                    'rank' => 3,
                    'value' => 76
                ],
                'fr_FR' => [
                    'rank' => 2,
                    'value' => 84
                ],
            ],
        ];

        $this->repository->save([
            new ProductAxisRates(
                new AxisCode('consistency'),
                new ProductId(123),
                new \DateTimeImmutable(),
                $consistencyRates
            ),
            new ProductAxisRates(
                new AxisCode('enrichment'),
                new ProductId(456),
                new \DateTimeImmutable(),
                $enrichmentRates
            )
        ]);

        $productAxisRates = $this->findAllProductAxisRates();

        $this->assertCount(2, $productAxisRates);
        $this->assertSame(123, (int) $productAxisRates[0]['product_id']);
        $this->assertEqualsCanonicalizing($expectedConsistencyRates, json_decode($productAxisRates[0]['rates'], true));
        $this->assertSame(456, (int) $productAxisRates[1]['product_id']);
        $this->assertEqualsCanonicalizing($expectedEnrichmentRates, json_decode($productAxisRates[1]['rates'], true));

        $updatedConsistencyRates = (new ChannelLocaleRateCollection())
            ->addRate($channelEcommerce, $localeEn, new Rate(68))
            ->addRate($channelEcommerce, $localeFr, new Rate(93))
            ->addRate($channelPrint, $localeEn, new Rate(23))
            ->addRate($channelPrint, $localeFr, new Rate(42))
        ;
        $expectedUpdatedConsistencyRates = [
            'ecommerce' => [
                'en_US' => [
                    'rank' => 4,
                    'value' => 68
                ],
                'fr_FR' => [
                    'rank' => 1,
                    'value' => 93
                ],
            ],
            'print' => [
                'en_US' => [
                    'rank' => 5,
                    'value' => 23
                ],
                'fr_FR' => [
                    'rank' => 5,
                    'value' => 42
                ],
            ],
        ];
        $this->repository->save([
            new ProductAxisRates(
                new AxisCode('consistency'),
                new ProductId(123),
                new \DateTimeImmutable(),
                $updatedConsistencyRates
            )
        ]);

        $productAxisRates = $this->findAllProductAxisRates();

        $this->assertCount(2, $productAxisRates);
        $this->assertSame(123, (int) $productAxisRates[0]['product_id']);
        $this->assertEqualsCanonicalizing($expectedUpdatedConsistencyRates, json_decode($productAxisRates[0]['rates'], true));
        $this->assertSame(456, (int) $productAxisRates[1]['product_id']);
        $this->assertEqualsCanonicalizing($expectedEnrichmentRates, json_decode($productAxisRates[1]['rates'], true));
    }

    public function test_it_purges_product_axis_rates_older_than_a_given_date()
    {
        $consistency = new AxisCode('consistency');
        $productAxisRates = [
            'product_123_consistency_last_rates' => new ProductAxisRates(
                $consistency,
                new ProductId(123),
                new \DateTimeImmutable('2019-12-17'),
                new ChannelLocaleRateCollection()
            ),
            'product_123_consistency_young_rates' => new ProductAxisRates(
                $consistency,
                new ProductId(123),
                new \DateTimeImmutable('2019-12-16'),
                new ChannelLocaleRateCollection()
            ),
            'product_123_consistency_old_rates' => new ProductAxisRates(
                $consistency,
                new ProductId(123),
                new \DateTimeImmutable('2019-12-15'),
                new ChannelLocaleRateCollection()
            ),
            'product_123_enrichment_old_but_last_rates' => new ProductAxisRates(
                $consistency,
                new ProductId(123),
                new \DateTimeImmutable('2019-12-23'),
                new ChannelLocaleRateCollection()
            ),
            'product_42_consistency_last_rates' => new ProductAxisRates(
                $consistency,
                new ProductId(42),
                new \DateTimeImmutable('2019-11-28'),
                new ChannelLocaleRateCollection()
            ),
            'product_42_consistency_old_rates' =>
                new ProductAxisRates(
                $consistency,
                new ProductId(42),
                new \DateTimeImmutable('2019-11-27'),
                new ChannelLocaleRateCollection()
            )
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

    private function assertProductAxisRatesExists(ProductAxisRates $productAxisRates): void
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
                'product_id' => $productAxisRates->getProductId()->toInt(),
                'axis_code' => $productAxisRates->getAxisCode(),
                'evaluated_at' => $productAxisRates->getEvaluatedAt()->format('Y-m-d')
            ]
        );

        $this->assertTrue((bool) $stmt->fetchColumn());
    }
}
