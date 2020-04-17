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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Consistency;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetCriteriaEvaluationsByProductIdQueryInterface;
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

    /** @var Axis[] */
    private $axes;

    /** @var ComputeAxisRates */
    private $computeAxisRates;

    public function __construct(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        ProductAxisRateRepositoryInterface $productAxisRateRepository,
        ComputeAxisRates $computeAxisRates,
        Clock $clock
    ) {
        $this->getCriteriaEvaluationsByProductIdQuery = $getCriteriaEvaluationsByProductIdQuery;
        $this->productAxisRateRepository = $productAxisRateRepository;
        $this->computeAxisRates = $computeAxisRates;
        $this->clock = $clock;
        $this->axes = [
            new Enrichment(),
            new Consistency(),
        ];
    }

    public function consolidate(array $productIds)
    {
        $currentDateTime = $this->clock->getCurrentTime();
        $productsAxesRates = [];

        foreach ($productIds as $productId) {
            $productId = new ProductId($productId);
            $criteriaEvaluations = $this->getCriteriaEvaluationsByProductIdQuery->execute($productId);
            foreach ($this->axes as $axis) {
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
