<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductScoresByIdentifiersQuery;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductScoresQuery implements GetProductScoresQueryInterface
{
    public function __construct(
        private GetProductScoresByIdentifiersQuery $getProductScoresByIdentifiersQuery,
        private GetScoresByCriteriaStrategy $getScoresByCriteria,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function byProductIdentifiers(array $productIdentifiers): array
    {
        $scoresByIdentifiers = $this->getProductScoresByIdentifiersQuery->byProductIdentifiers($productIdentifiers);

        return array_map(
            fn (Read\Scores $scores) => $this->qualityScoreCollection(($this->getScoresByCriteria)($scores)),
            $scoresByIdentifiers
        );
    }

    public function byProductIdentifier(string $productIdentifier): QualityScoreCollection
    {
        $scores = $this->getProductScoresByIdentifiersQuery->byProductIdentifier($productIdentifier);

        return $this->qualityScoreCollection(($this->getScoresByCriteria)($scores));
    }

    private function qualityScoreCollection(ChannelLocaleRateCollection $channelLocaleRateCollection): QualityScoreCollection
    {
        $qualityScores = $channelLocaleRateCollection->mapWith(static fn (Rate $rate) => new QualityScore($rate->toLetter(), $rate->toInt()));
        return new QualityScoreCollection($qualityScores);
    }
}
