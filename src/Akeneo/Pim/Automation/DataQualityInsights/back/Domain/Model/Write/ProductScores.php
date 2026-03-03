<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;

/**
 * Warning, this class is misnamed, because it contains Product or ProductModel scores
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductScores
{
    public function __construct(
        private ProductEntityIdInterface $entityId,
        private \DateTimeImmutable $evaluatedAt,
        private ChannelLocaleRateCollection $scores,
        private ChannelLocaleRateCollection $scoresPartialCriteria,
    ) {
    }

    public function getEntityId(): ProductEntityIdInterface
    {
        return $this->entityId;
    }

    public function getEvaluatedAt(): \DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function getScores(): ChannelLocaleRateCollection
    {
        return $this->scores;
    }

    public function getScoresPartialCriteria(): ChannelLocaleRateCollection
    {
        return $this->scoresPartialCriteria;
    }
}
