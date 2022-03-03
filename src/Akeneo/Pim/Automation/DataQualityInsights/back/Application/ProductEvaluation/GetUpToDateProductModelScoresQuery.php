<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUpToDateProductModelScoresQuery
{
    public function __construct(
        private HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery
    ) {
    }

    public function byProductModelId(ProductId $productModelId): ChannelLocaleRateCollection
    {
        if ($this->hasUpToDateEvaluationQuery->forProductId($productModelId)) {
            return $this->getProductModelScoresQuery->byProductModelId($productModelId);
        }

        return new ChannelLocaleRateCollection();
    }
}
