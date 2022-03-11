<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductScoresByIdentifiersQuery;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScoreCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductScoresQuery implements GetProductScoresQueryInterface
{
    public function __construct(private GetProductScoresByIdentifiersQuery $getProductScoresByIdentifiersQuery) {
    }

    public function byProductIdentifiers(array $productIdentifiers): array
    {
        $channelLocaleRateCollections = $this->getProductScoresByIdentifiersQuery->byProductIdentifiers($productIdentifiers);
        return array_map(
            fn (ChannelLocaleRateCollection $channelLocaleRateCollection) => $this->productScoreCollection($channelLocaleRateCollection),
            $channelLocaleRateCollections
        );
    }

    public function byProductIdentifier(string $productIdentifier): ProductScoreCollection
    {
        $channelLocaleRateCollection = $this->getProductScoresByIdentifiersQuery->byProductIdentifier($productIdentifier);
        return $this->productScoreCollection($channelLocaleRateCollection);
    }

    private function productScoreCollection(ChannelLocaleRateCollection $channelLocaleRateCollection): ProductScoreCollection
    {
        $productScores = $channelLocaleRateCollection->mapWith(static fn (Rate $rate) => new ProductScore($rate->toLetter(), $rate->toInt()));
        return new ProductScoreCollection($productScores);
    }
}
