<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

class GetProductAxesRates
{
    /** @var ComputeAxisRatesInterface */
    private $computeEnrichmentRates;

    /** @var GetLatestCriteriaEvaluationsByProductIdQueryInterface */
    private $getLatestCriteriaEvaluationsByProductIdQuery;

    /** @var ComputeAxisRatesInterface*/
    private $computeConsistencyRates;

    public function __construct(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        ComputeAxisRatesInterface $computeEnrichmentRates,
        ComputeAxisRatesInterface $computeConsistencyRates
    ) {
        $this->computeEnrichmentRates = $computeEnrichmentRates;
        $this->getLatestCriteriaEvaluationsByProductIdQuery = $getLatestCriteriaEvaluationsByProductIdQuery;
        $this->computeConsistencyRates = $computeConsistencyRates;
    }

    public function get(ProductId $productId): array
    {
        $latestCriteriaEvaluations = $this->getLatestCriteriaEvaluationsByProductIdQuery->execute($productId);
        $enrichmentRates = $this->computeEnrichmentRates->compute($latestCriteriaEvaluations);
        $consistencyRates = $this->computeConsistencyRates->compute($latestCriteriaEvaluations);

        return [
            'enrichment' => [
                'code' => 'enrichment',
                'rates' => $enrichmentRates->toArrayString(),
            ],
            'consistency' => [
                'code' => 'consistency',
                'rates' => $consistencyRates->toArrayString(),
            ],
        ];
    }
}
