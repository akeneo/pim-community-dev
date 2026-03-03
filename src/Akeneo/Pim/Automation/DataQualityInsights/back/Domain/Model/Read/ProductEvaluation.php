<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductEvaluation
{
    public function __construct(
        private ProductUuid                   $productUuid,
        private ChannelLocaleRateCollection   $scores,
        private CriterionEvaluationCollection $criteriaEvaluations
    ) {
    }

    public function getProductUuid(): ProductUuid
    {
        return $this->productUuid;
    }

    public function getScores(): ChannelLocaleRateCollection
    {
        return $this->scores;
    }

    public function getCriteriaEvaluations(): CriterionEvaluationCollection
    {
        return $this->criteriaEvaluations;
    }
}
