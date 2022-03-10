<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class GetNormalizedProductQualityScores implements GetNormalizedQualityScoresInterface
{
    public function __construct(
        private GetLatestProductScoresQueryInterface $getLatestProductScoresQuery,
        private FeatureFlag $dataQualityInsightsFeature
    ) {
    }

    public function __invoke(string $productIdentifier, string $channel = null, array $locales = []): array
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return [];
        }

        $productScoreCollection = $this->getLatestProductScoresQuery->byProductIdentifier($productIdentifier);
        $productScoreCollection = $this->filterProductQualityScores($productScoreCollection, $channel, $locales);

        return $productScoreCollection->productScores;
    }


    private function filterProductQualityScores(ProductScoreCollection $productScoreCollection, string $channel, array $locales): ProductScoreCollection
    {
        if (null === $channel && empty($locales)) {
            return $productScoreCollection;
        }

        $filteredQualityScores = [];
        foreach ($productScoreCollection->productScores as $scoreChannel => $scoresLocales) {
            if ($channel !== null && $channel !== $scoreChannel) {
                continue;
            }
            foreach ($scoresLocales as $scoreLocale => $score) {
                if (empty($locales) || in_array($scoreLocale, $locales)) {
                    $filteredQualityScores[$scoreChannel][$scoreLocale] = $score;
                }
            }
        }

        return new ProductScoreCollection($filteredQualityScores);
    }
}
