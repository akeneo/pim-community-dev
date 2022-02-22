<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUpToDateLatestProductScoresQuery implements GetLatestProductScoresQueryInterface
{
    private HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery;

    private GetLatestProductScoresQueryInterface $getLatestProductScoresQuery;

    public function __construct(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        GetLatestProductScoresQueryInterface $getLatestProductScoresQuery
    ) {
        $this->hasUpToDateEvaluationQuery = $hasUpToDateEvaluationQuery;
        $this->getLatestProductScoresQuery = $getLatestProductScoresQuery;
    }

    public function byProductId(ProductId $productId): ChannelLocaleRateCollection
    {
        if ($this->hasUpToDateEvaluationQuery->forProductId($productId)) {
            return $this->getLatestProductScoresQuery->byProductId($productId);
        }

        return new ChannelLocaleRateCollection();
    }

    public function byProductIds(ProductIdCollection $productIdCollection): array
    {
        $upToDateProducts = $this->hasUpToDateEvaluationQuery->forProductIdCollection($productIdCollection);

        return is_null($upToDateProducts) ? [] : $this->getLatestProductScoresQuery->byProductIds($upToDateProducts);
    }
}
