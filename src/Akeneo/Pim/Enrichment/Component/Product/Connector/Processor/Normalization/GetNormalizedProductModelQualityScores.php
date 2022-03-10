<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductModelScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetLatestProductScoresQuery;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\GetNormalizedQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class GetNormalizedProductModelQualityScores implements GetNormalizedQualityScoresInterface
{
    public function __construct(
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private FeatureFlag $dataQualityInsightsFeature
    ) {
    }

    public function __invoke(string $code, string $channel = null, array $locales = []): array
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return [];
        }

        $productModelScoreCollection = $this->getProductModelScoresQuery->byProductModelCode($code);
        $productModelScoreCollection = $this->filterProductModelQualityScores($productModelScoreCollection, $channel, $locales);

        return $productModelScoreCollection->productModelScores;
    }


    private function filterProductModelQualityScores(ProductModelScoreCollection $productModelScoreCollection, string $channel, array $locales): ProductModelScoreCollection
    {
        if (null === $channel && empty($locales)) {
            return $productModelScoreCollection;
        }

        $filteredQualityScores = [];
        foreach ($productModelScoreCollection->productModelScores as $scoreChannel => $scoresLocales) {
            if ($channel !== null && $channel !== $scoreChannel) {
                continue;
            }
            foreach ($scoresLocales as $scoreLocale => $score) {
                if (empty($locales) || in_array($scoreLocale, $locales)) {
                    $filteredQualityScores[$scoreChannel][$scoreLocale] = $score;
                }
            }
        }

        return new ProductModelScoreCollection($filteredQualityScores);
    }
}
