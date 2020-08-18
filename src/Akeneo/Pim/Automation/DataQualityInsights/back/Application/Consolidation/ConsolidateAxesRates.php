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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\AxisRegistryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

class ConsolidateAxesRates
{
    /** @var GetCriteriaEvaluationsByProductIdQueryInterface */
    private $getCriteriaEvaluationsByProductIdQuery;

    /** @var ProductAxisRateRepositoryInterface */
    private $productAxisRateRepository;

    /** @var Clock */
    private $clock;

    /** @var ComputeAxisRates */
    private $computeAxisRates;

    private $axisRegistry;

    public function __construct(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        ProductAxisRateRepositoryInterface $productAxisRateRepository,
        ComputeAxisRates $computeAxisRates,
        Clock $clock,
        AxisRegistryInterface $axisRegistry
    ) {
        $this->getCriteriaEvaluationsByProductIdQuery = $getCriteriaEvaluationsByProductIdQuery;
        $this->productAxisRateRepository = $productAxisRateRepository;
        $this->computeAxisRates = $computeAxisRates;
        $this->clock = $clock;
        $this->axisRegistry = $axisRegistry;
    }

    public function consolidate(array $productIds)
    {
        $currentDateTime = $this->clock->getCurrentTime();
        $productsAxesRates = [];

        foreach ($productIds as $productId) {
            $productId = new ProductId($productId);
            $criteriaEvaluations = $this->getCriteriaEvaluationsByProductIdQuery->execute($productId);
            foreach ($this->axisRegistry->all() as $axis) {
                $productsAxesRates[] = new Write\ProductAxisRates(
                    $axis->getCode(),
                    $productId,
                    $currentDateTime,
                    $this->computeAxisRates->compute($axis, $criteriaEvaluations)
                );
            }
        }

        $this->productAxisRateRepository->save($productsAxesRates);
    }
}
