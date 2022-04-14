<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductScores
{
    public function __construct(
        private ProductEntityIdInterface $productId,
        private \DateTimeImmutable $evaluatedAt,
        private ChannelLocaleRateCollection $scores,
        private ChannelLocaleRateCollection $scoresPartialCriteria,
    ) {
    }

    public function getProductId(): ProductEntityIdInterface
    {
        return $this->productId;
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
