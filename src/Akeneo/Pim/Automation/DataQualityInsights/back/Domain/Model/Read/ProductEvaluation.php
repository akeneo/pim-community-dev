<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductEvaluation
{
    private ProductId $productId;

    private ChannelLocaleRateCollection $scores;

    private CriterionEvaluationCollection $criteriaEvaluations;

    public function __construct(ProductId $productId, ChannelLocaleRateCollection $scores, CriterionEvaluationCollection $criteriaEvaluations)
    {
        $this->productId = $productId;
        $this->scores = $scores;
        $this->criteriaEvaluations = $criteriaEvaluations;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
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
