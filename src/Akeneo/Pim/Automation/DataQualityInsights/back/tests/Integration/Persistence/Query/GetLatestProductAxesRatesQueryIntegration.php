<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\back\tests\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetLatestProductAxesRatesQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductAxisRateRepository;
use Akeneo\Test\Integration\TestCase;

final class GetLatestProductAxesRatesQueryIntegration extends TestCase
{
    /** @var GetLatestProductAxesRatesQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetLatestProductAxesRatesQuery::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_latest_axes_rates_by_product_ids()
    {
        $rates = [
            'product_42_consistency_latest_rates' => [
                'product_id' => 42,
                'axis' => 'consistency',
                'evaluated_at' => new \DateTime('2020-01-08'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 1, 'rate' => 96],
                        'fr_FR' => ['rank' => 5, 'rate' => 36],
                    ]
                ]
            ],
            'product_42_consistency_previous_rates' => [
                'product_id' => 42,
                'axis' => 'consistency',
                'evaluated_at' => new \DateTime('2020-01-07'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 3, 'rate' => 76],
                        'fr_FR' => ['rank' => 4, 'rate' => 67],
                    ]
                ]
            ],
            'product_42_enrichment_latest_rates' => [
                'product_id' => 42,
                'axis' => 'enrichment',
                'evaluated_at' => new \DateTime('2020-01-08'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 2, 'rate' => 84],
                        'fr_FR' => ['rank' => 5, 'rate' => 35],
                    ]
                ]
            ],
            'product_123_enrichment_latest_rates' => [
                'product_id' => 123,
                'axis' => 'enrichment',
                'evaluated_at' => new \DateTime('2020-01-09'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 1, 'rate' => 100],
                        'fr_FR' => ['rank' => 1, 'rate' => 95],
                    ]
                ]
            ],
            'product_123_enrichment_previous_rates' => [
                'product_id' => 123,
                'axis' => 'enrichment',
                'evaluated_at' => new \DateTime('2020-01-08'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 2, 'rate' => 81],
                        'fr_FR' => ['rank' => 1, 'rate' => 95],
                    ]
                ]
            ],
            'other_product_rates' => [
                'product_id' => 456,
                'axis' => 'enrichment',
                'evaluated_at' => new \DateTime('2020-01-08'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 2, 'rate' => 87],
                        'fr_FR' => ['rank' => 1, 'rate' => 95],
                    ]
                ]
            ]
        ];
        $repository = $this->getRepository();
        $repository->save(array_values($rates));

        $expectedRates = [
            42 => [
                'consistency' => $rates['product_42_consistency_latest_rates']['rates'],
                'enrichment' => $rates['product_42_enrichment_latest_rates']['rates'],
            ],
            123 => [
                'enrichment' => $rates['product_123_enrichment_latest_rates']['rates'],
            ],
        ];

        $productAxesRates = $this->query->byProductIds([new ProductId(42), new ProductId(123), new ProductId(321)]);

        $this->assertEqualsCanonicalizing($expectedRates, $productAxesRates);
    }

    private function getRepository(): ProductAxisRateRepository
    {
        return $this->get(ProductAxisRateRepository::class);
    }
}
