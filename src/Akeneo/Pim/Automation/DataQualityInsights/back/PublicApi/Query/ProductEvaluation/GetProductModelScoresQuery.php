<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelScoresByCodesQuery;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductModelScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductModelScoreCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelScoresQuery implements GetProductModelScoresQueryInterface
{
    public function __construct(private GetProductModelScoresByCodesQuery $getProductModelScoresByCodesQuery)
    {
    }

    public function byProductModelCodes(array $productModelCodes): array
    {
        $channelLocaleRateCollections = $this->getProductModelScoresByCodesQuery->byProductModelCodes($productModelCodes);
        return array_map(
            fn (ChannelLocaleRateCollection $channelLocaleRateCollection) => $this->productModelScoreCollection($channelLocaleRateCollection),
            $channelLocaleRateCollections
        );
    }

    public function byProductModelCode(string $productModelCode): ProductModelScoreCollection
    {
        $channelLocaleRateCollection = $this->getProductModelScoresByCodesQuery->byProductModelCode($productModelCode);
        return $this->productModelScoreCollection($channelLocaleRateCollection);
    }

    private function productModelScoreCollection(ChannelLocaleRateCollection $channelLocaleRateCollection): ProductModelScoreCollection
    {
        $productModelScores = $channelLocaleRateCollection->mapWith(static fn (Rate $rate) => new ProductModelScore($rate->toLetter(), $rate->toInt()));
        return new ProductModelScoreCollection($productModelScores);
    }
}
