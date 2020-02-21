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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Consistency;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetLatestProductAxesRanksQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductAxisRateRepository;
use Akeneo\Test\Integration\TestCase;

final class GetLatestProductAxesRanksQueryIntegration extends TestCase
{
    /** @var GetLatestProductAxesRanksQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetLatestProductAxesRanksQuery::class);
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
                        'en_US' => ['rank' => 1, 'value' => 96],
                        'fr_FR' => ['rank' => 5, 'value' => 36],
                    ]
                ]
            ],
            'product_42_consistency_previous_rates' => [
                'product_id' => 42,
                'axis' => 'consistency',
                'evaluated_at' => new \DateTime('2020-01-07'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 3, 'value' => 76],
                        'fr_FR' => ['rank' => 4, 'value' => 67],
                    ]
                ]
            ],
            'product_42_enrichment_latest_rates' => [
                'product_id' => 42,
                'axis' => 'enrichment',
                'evaluated_at' => new \DateTime('2020-01-08'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 2, 'value' => 84],
                        'fr_FR' => ['rank' => 5, 'value' => 35],
                    ]
                ]
            ],
            'product_123_enrichment_latest_rates' => [
                'product_id' => 123,
                'axis' => 'enrichment',
                'evaluated_at' => new \DateTime('2020-01-09'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 1, 'value' => 100],
                        'fr_FR' => ['rank' => 1, 'value' => 95],
                    ]
                ]
            ],
            'product_123_enrichment_previous_rates' => [
                'product_id' => 123,
                'axis' => 'enrichment',
                'evaluated_at' => new \DateTime('2020-01-08'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 2, 'value' => 81],
                        'fr_FR' => ['rank' => 1, 'value' => 95],
                    ]
                ]
            ],
            'other_product_rates' => [
                'product_id' => 456,
                'axis' => 'enrichment',
                'evaluated_at' => new \DateTime('2020-01-08'),
                'rates' => [
                    'mobile' => [
                        'en_US' => ['rank' => 2, 'value' => 87],
                        'fr_FR' => ['rank' => 1, 'value' => 95],
                    ]
                ]
            ]
        ];
        $repository = $this->getRepository();
        $repository->save(array_values($rates));

        $consistency = new Consistency();
        $enrichment = new Enrichment();
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $expectedRates = [
            42 => (new AxisRankCollection())
                ->add($consistency->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelMobile, $localeEn, Rank::fromInt($rates['product_42_consistency_latest_rates']['rates']['mobile']['en_US']['rank']))
                    ->addRank($channelMobile, $localeFr, Rank::fromInt($rates['product_42_consistency_latest_rates']['rates']['mobile']['fr_FR']['rank']))
                )
                ->add($enrichment->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelMobile, $localeEn, Rank::fromInt($rates['product_42_enrichment_latest_rates']['rates']['mobile']['en_US']['rank']))
                    ->addRank($channelMobile, $localeFr, Rank::fromInt($rates['product_42_enrichment_latest_rates']['rates']['mobile']['fr_FR']['rank']))
                ),
            123 => (new AxisRankCollection())
                ->add($consistency->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelMobile, $localeEn, Rank::fromInt($rates['product_123_enrichment_latest_rates']['rates']['mobile']['en_US']['rank']))
                    ->addRank($channelMobile, $localeFr, Rank::fromInt($rates['product_123_enrichment_latest_rates']['rates']['mobile']['fr_FR']['rank']))
                )
        ];

        $productAxesRates = $this->query->byProductIds([new ProductId(42), new ProductId(123), new ProductId(321)]);

        $this->assertEqualsCanonicalizing($expectedRates, $productAxesRates);
    }

    private function getRepository(): ProductAxisRateRepository
    {
        return $this->get(ProductAxisRateRepository::class);
    }
}
