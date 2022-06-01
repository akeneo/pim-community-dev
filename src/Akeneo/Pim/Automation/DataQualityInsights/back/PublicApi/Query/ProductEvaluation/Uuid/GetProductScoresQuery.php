<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\Uuid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\Uuid\GetProductScoresByUuidsQuery;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductScoresQuery
{
    public function __construct(
        private GetProductScoresByUuidsQuery $getProductScoresByUuidsQuery,
        private GetScoresByCriteriaStrategy $getScoresByCriteria,
    ) {
    }

    /**
     * @param UuidInterface[] $productUuids
     * @return QualityScoreCollection[]
     */
    public function byProductUuids(array $productUuids): array
    {
        $scoresByUuids = $this->getProductScoresByUuidsQuery->byProductUuids($productUuids);

        return \array_map(
            fn (Read\Scores $scores) => $this->qualityScoreCollection(($this->getScoresByCriteria)($scores)),
            $scoresByUuids
        );
    }

    /**
     * @param UuidInterface $productUuid
     * @return QualityScoreCollection
     */
    public function byProductUuid(UuidInterface $productUuid): QualityScoreCollection
    {
        $scores = $this->getProductScoresByUuidsQuery->byProductUuid($productUuid);

        return $this->qualityScoreCollection(($this->getScoresByCriteria)($scores));
    }

    /**
     * @param ChannelLocaleRateCollection $channelLocaleRateCollection
     * @return QualityScoreCollection
     */
    private function qualityScoreCollection(ChannelLocaleRateCollection $channelLocaleRateCollection): QualityScoreCollection
    {
        $qualityScores = $channelLocaleRateCollection->mapWith(static fn (Rate $rate) => new QualityScore($rate->toLetter(), $rate->toInt()));
        return new QualityScoreCollection($qualityScores);
    }
}
