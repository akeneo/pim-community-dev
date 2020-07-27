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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Consistency;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetLatestAxesRatesQuery;
use Akeneo\Test\Integration\TestCase;

final class GetLatestAxesRatesQueryIntegration extends TestCase
{
    /** @var GetLatestAxesRatesQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo.pim.automation.data_quality_insights.query.get_latest_product_axes_rates');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_latest_axes_rates_of_a_product_by_its_id()
    {
        $consistency = new Consistency();
        $enrichment = new Enrichment();
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $rates = [
            'product_42_consistency_latest_rates' =>
                new ProductAxisRates(
                new AxisCode('consistency'),
                new ProductId(42),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36))
            ),
            'product_42_consistency_previous_rates' => new ProductAxisRates(
                new AxisCode('consistency'),
                new ProductId(42),
                new \DateTimeImmutable('2020-01-07'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(76))
                    ->addRate($channelMobile, $localeFr, new Rate(67))
            ),
            'product_42_enrichment_latest_rates' => new ProductAxisRates(
                new AxisCode('enrichment'),
                new ProductId(42),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(84))
                    ->addRate($channelMobile, $localeFr, new Rate(35))
            ),
            'other_product_rates' =>new ProductAxisRates(
                new AxisCode('enrichment'),
                new ProductId(456),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
        ];
        $repository = $this->getRepository();
        $repository->save(array_values($rates));

        $expectedRates = (new AxisRateCollection())
            ->add($consistency->getCode(), (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, $rates['product_42_consistency_latest_rates']->getRates()->getByChannelAndLocale($channelMobile, $localeEn))
                ->addRate($channelMobile, $localeFr, $rates['product_42_consistency_latest_rates']->getRates()->getByChannelAndLocale($channelMobile, $localeFr))
            )
            ->add($enrichment->getCode(), (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, $rates['product_42_enrichment_latest_rates']->getRates()->getByChannelAndLocale($channelMobile, $localeEn))
                ->addRate($channelMobile, $localeFr, $rates['product_42_enrichment_latest_rates']->getRates()->getByChannelAndLocale($channelMobile, $localeFr))
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

    private function getRepository(): ProductAxisRateRepositoryInterface
    {
        return $this->get('akeneo.pim.automation.data_quality_insights.repository.product_axis_rate');
    }
}
