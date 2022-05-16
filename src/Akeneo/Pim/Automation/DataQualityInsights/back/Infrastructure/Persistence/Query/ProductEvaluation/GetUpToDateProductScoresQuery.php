<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUpToDateProductScoresQuery implements GetProductScoresQueryInterface
{
    private HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery;

    private GetProductScoresQueryInterface $getProductScoresQuery;

    public function __construct(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        GetProductScoresQueryInterface      $getProductScoresQuery
    ) {
        $this->hasUpToDateEvaluationQuery = $hasUpToDateEvaluationQuery;
        $this->getProductScoresQuery = $getProductScoresQuery;
    }

    public function byProductId(ProductEntityIdInterface $productId): Read\Scores
    {
        if ($this->hasUpToDateEvaluationQuery->forProductId($productId)) {
            return $this->getProductScoresQuery->byProductId($productId);
        }

        return new Read\Scores(new ChannelLocaleRateCollection(), new ChannelLocaleRateCollection());
    }

    public function byProductIds(ProductEntityIdCollection $productIdCollection): array
    {
        $upToDateProducts = $this->hasUpToDateEvaluationQuery->forProductIdCollection($productIdCollection);

        return is_null($upToDateProducts) ? [] : $this->getProductScoresQuery->byProductIds($upToDateProducts);
    }
}
