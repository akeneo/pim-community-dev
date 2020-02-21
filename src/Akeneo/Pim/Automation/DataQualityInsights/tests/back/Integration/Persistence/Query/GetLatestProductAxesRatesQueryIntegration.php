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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
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

    public function test_it_returns_the_latest_axes_rates_of_a_product_by_its_id()
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

        $expectedRates = (new AxisRateCollection())
            ->add($consistency->getCode(), (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate($rates['product_42_consistency_latest_rates']['rates']['mobile']['en_US']['value']))
                ->addRate($channelMobile, $localeFr, new Rate($rates['product_42_consistency_latest_rates']['rates']['mobile']['fr_FR']['value']))
            )
            ->add($enrichment->getCode(), (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate($rates['product_42_enrichment_latest_rates']['rates']['mobile']['en_US']['value']))
                ->addRate($channelMobile, $localeFr, new Rate($rates['product_42_enrichment_latest_rates']['rates']['mobile']['fr_FR']['value']))
            )
        ;

        $productAxesRates = $this->query->byProductId(new ProductId(42));

        $this->assertEqualsCanonicalizing($expectedRates, $productAxesRates);
    }

    public function test_it_returns_an_empty_collection_if_there_are_no_axes_rates()
    {
        $productAxesRates = $this->query->byProductId(new ProductId(42));
        $this->assertEquals(new AxisRateCollection(), $productAxesRates);
    }

    private function getRepository(): ProductAxisRateRepository
    {
        return $this->get(ProductAxisRateRepository::class);
    }
}
