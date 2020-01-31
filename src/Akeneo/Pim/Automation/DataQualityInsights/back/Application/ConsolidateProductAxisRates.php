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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

class ConsolidateProductAxisRates
{
    /** @var GetLatestCriteriaEvaluationsByProductIdQueryInterface */
    private $getLatestCriteriaEvaluationsByProductIdQuery;

    /** @var ComputeAxisRatesInterface */
    private $computeEnrichmentRates;

    /** @var ComputeAxisRatesInterface */
    private $computeConsistencyRates;

    /** @var ProductAxisRateRepositoryInterface */
    private $productAxisRateRepository;

    /** @var Clock */
    private $clock;

    public function __construct(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        ComputeAxisRatesInterface $computeEnrichmentRates,
        ComputeAxisRatesInterface $computeConsistencyRates,
        ProductAxisRateRepositoryInterface $productAxisRateRepository,
        Clock $clock
    ) {
        $this->getLatestCriteriaEvaluationsByProductIdQuery = $getLatestCriteriaEvaluationsByProductIdQuery;
        $this->computeEnrichmentRates = $computeEnrichmentRates;
        $this->computeConsistencyRates = $computeConsistencyRates;
        $this->productAxisRateRepository = $productAxisRateRepository;
        $this->clock = $clock;
    }

    public function consolidate(array $productIds)
    {
        $currentDateTime = $this->clock->getCurrentTime();
        $productAxisRates = [];

        foreach ($productIds as $productId) {
            $latestCriteriaEvaluations = $this->getLatestCriteriaEvaluationsByProductIdQuery->execute(new ProductId($productId));
            $enrichmentRates = $this->computeEnrichmentRates->compute($latestCriteriaEvaluations);
            $consistencyRates = $this->computeConsistencyRates->compute($latestCriteriaEvaluations);

            $productAxisRates[] = [
                'evaluated_at' => $currentDateTime,
                'product_id' => $productId,
                'axis' => 'enrichment',
                'rates' => $enrichmentRates->formatForConsolidation(),
            ];
            $productAxisRates[] = [
                'evaluated_at' => $currentDateTime,
                'product_id' => $productId,
                'axis' => 'consistency',
                'rates' => $consistencyRates->formatForConsolidation(),
            ];
        }

        $this->productAxisRateRepository->save($productAxisRates);
    }
}
