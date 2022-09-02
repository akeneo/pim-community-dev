<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelScoresByCodesQuery;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelScoresQuery implements GetProductModelScoresQueryInterface
{
    public function __construct(
        private GetProductModelScoresByCodesQuery $getProductModelScoresByCodesQuery,
        private GetScoresByCriteriaStrategy $getScoresByCriteria,
    ) {
    }

    public function byProductModelCodes(array $productModelCodes): array
    {
        $scoresByIdentifiers = $this->getProductModelScoresByCodesQuery->byProductModelCodes($productModelCodes);

        return array_map(
            fn (Read\Scores $scores) => $this->qualityScoreCollection(($this->getScoresByCriteria)($scores)),
            $scoresByIdentifiers
        );
    }

    public function byProductModelCode(string $productModelCode): QualityScoreCollection
    {
        $scores = $this->getProductModelScoresByCodesQuery->byProductModelCode($productModelCode);

        return $this->qualityScoreCollection(($this->getScoresByCriteria)($scores));
    }

    private function qualityScoreCollection(ChannelLocaleRateCollection $channelLocaleRateCollection): QualityScoreCollection
    {
        $productModelScores = $channelLocaleRateCollection->mapWith(static fn (Rate $rate) => new QualityScore($rate->toLetter(), $rate->toInt()));
        return new QualityScoreCollection($productModelScores);
    }
}
