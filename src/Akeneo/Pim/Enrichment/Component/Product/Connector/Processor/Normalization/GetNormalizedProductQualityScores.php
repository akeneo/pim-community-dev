<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class GetNormalizedProductQualityScores implements GetNormalizedQualityScoresInterface
{
    public function __construct(
        private GetProductScoresQueryInterface $getProductScoresQuery,
        private FeatureFlag $dataQualityInsightsFeature
    ) {
    }

    public function __invoke(string|UuidInterface $productUuid, string $channel = null, array $locales = []): array
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return [];
        }

        Assert::isInstanceOf($productUuid, UuidInterface::class);

        $qualityScoreCollection = $this->getProductScoresQuery->byProductUuid($productUuid);
        $qualityScoreCollection = $this->filterProductQualityScores($qualityScoreCollection, $channel, $locales);

        return $this->normalizeQualityScores($qualityScoreCollection);
    }

    private function filterProductQualityScores(QualityScoreCollection $qualityScoreCollection, ?string $channel, array $locales): QualityScoreCollection
    {
        if (null === $channel && empty($locales)) {
            return $qualityScoreCollection;
        }

        $filteredQualityScores = [];
        foreach ($qualityScoreCollection->qualityScores as $scoreChannel => $scoresLocales) {
            if ($channel !== null && $channel !== $scoreChannel) {
                continue;
            }
            foreach ($scoresLocales as $scoreLocale => $score) {
                if (empty($locales) || in_array($scoreLocale, $locales)) {
                    $filteredQualityScores[$scoreChannel][$scoreLocale] = $score;
                }
            }
        }

        return new QualityScoreCollection($filteredQualityScores);
    }

    private function normalizeQualityScores(QualityScoreCollection $qualityScoreCollection): array
    {
        $normalizedQualityScores = [];

        foreach ($qualityScoreCollection->qualityScores as $channel => $localesScores) {
            /** @var QualityScore $score */
            foreach ($localesScores as $locale => $score) {
                $normalizedQualityScores[$channel][$locale] = $score->getLetter();
            }
        }

        return $normalizedQualityScores;
    }
}
