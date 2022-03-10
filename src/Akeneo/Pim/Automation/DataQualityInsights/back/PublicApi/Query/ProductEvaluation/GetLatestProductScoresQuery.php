<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQuery as DqiGetLatestProductScoresByIdentifiersQuery;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScoreCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetLatestProductScoresQuery implements GetLatestProductScoresQueryInterface
{
    private DqiGetLatestProductScoresByIdentifiersQuery $dqiGetLatestProductScoresByIdentifiersQuery;

    public function __construct(DqiGetLatestProductScoresByIdentifiersQuery $dqiGetLatestProductScoresByIdentifiersQuery)
    {
        $this->dqiGetLatestProductScoresByIdentifiersQuery = $dqiGetLatestProductScoresByIdentifiersQuery;
    }

    public function byProductIdentifiers(array $productIdentifiers): array
    {
        $channelLocaleRateCollections = $this->dqiGetLatestProductScoresByIdentifiersQuery->byProductIdentifiers($productIdentifiers);
        return array_map(
            fn (ChannelLocaleRateCollection $channelLocaleRateCollection) => $this->productScoreCollection($channelLocaleRateCollection),
            $channelLocaleRateCollections
        );
    }

    public function byProductIdentifier(string $productIdentifier): ProductScoreCollection
    {
        $channelLocaleRateCollection = $this->dqiGetLatestProductScoresByIdentifiersQuery->byProductIdentifier($productIdentifier);
        return $this->productScoreCollection($channelLocaleRateCollection);
    }

    private function productScoreCollection(ChannelLocaleRateCollection $channelLocaleRateCollection): ProductScoreCollection
    {
        $productScores = $channelLocaleRateCollection->mapWith(static fn (Rate $rate) => new ProductScore($rate->toLetter(), $rate->toInt()));
        return new ProductScoreCollection($productScores);
    }
}
