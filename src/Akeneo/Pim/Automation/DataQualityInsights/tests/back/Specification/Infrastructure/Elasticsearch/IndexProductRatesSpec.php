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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Domain\Model\Axis\Consistency;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

class IndexProductRatesSpec extends ObjectBehavior
{
    public function let(Client $esClient, GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery)
    {
        $this->beConstructedWith($esClient, $getLatestProductAxesRanksQuery);
    }

    public function it_indexes_product_rates(
        Client $esClient,
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery
    ) {
        $consistency = new Consistency();
        $enrichment = new Enrichment();
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');

        $getLatestProductAxesRanksQuery->byProductIds([new ProductId(123), new ProductId(456), new ProductId(42)])->willReturn([
            123 => (new AxisRankCollection())
                ->add($consistency->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(1))
                )
                ->add($enrichment->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(2))
                ),
            456 => (new AxisRankCollection())
                ->add($consistency->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(3))
                )
                ->add($enrichment->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(5))
                ),
        ]);

        $esClient->updateByQuery([
            'script' => [
                'source' => "ctx._source.rates = params",
                'params' => [
                    'consistency' => ['ecommerce' => ['en_US' => 1]],
                    'enrichment' => ['ecommerce' => ['en_US' => 2]],
                ],
            ],
            'query' => [
                'term' => [
                    'id' => 'product_123',
                ],
            ],
        ])->shouldBeCalled();
        $esClient->updateByQuery([
            'script' => [
                'source' => "ctx._source.rates = params",
                'params' => [
                    'consistency' => ['ecommerce' => ['en_US' => 3]],
                    'enrichment' => ['ecommerce' => ['en_US' => 5]],
                ],
            ],
            'query' => [
                'term' => [
                    'id' => 'product_456',
                ],
            ],
        ])->shouldBeCalled();

        $this->execute([123, 456, 42]);
    }
}
