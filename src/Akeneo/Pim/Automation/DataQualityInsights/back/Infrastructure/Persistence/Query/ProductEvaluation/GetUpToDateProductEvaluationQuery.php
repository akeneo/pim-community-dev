<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\AxisRegistryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class GetUpToDateProductEvaluationQuery implements GetProductEvaluationQueryInterface
{
    /** @var GetCriteriaEvaluationsByProductIdQueryInterface */
    private $getCriteriaEvaluationsByProductIdQuery;

    /** @var GetLatestAxesRatesQueryInterface */
    private $getLatestProductAxesRatesQuery;

    /** @var AxisRegistryInterface */
    private $axisRegistry;

    public function __construct(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        AxisRegistryInterface $axisRegistry
    ) {
        $this->getCriteriaEvaluationsByProductIdQuery = $getCriteriaEvaluationsByProductIdQuery;
        $this->getLatestProductAxesRatesQuery = $getLatestProductAxesRatesQuery;
        $this->axisRegistry = $axisRegistry;
    }

    public function execute(ProductId $productId): ProductEvaluation
    {
        $productAxesRates = $this->getLatestProductAxesRatesQuery->byProductId($productId);
        $productCriteriaEvaluations = $this->getCriteriaEvaluationsByProductIdQuery->execute($productId);

        $axesEvaluations = new AxisEvaluationCollection();
        foreach ($this->axisRegistry->all() as $axis) {
            $axisEvaluation = $this->buildAxisEvaluation($axis, $productAxesRates, $productCriteriaEvaluations);
            $axesEvaluations->add($axisEvaluation);
        }

        return new ProductEvaluation($productId, $axesEvaluations);
    }

    private function buildAxisEvaluation(Axis $axis, AxisRateCollection $axesRates, CriterionEvaluationCollection $productCriteriaEvaluations): AxisEvaluation
    {
        $axisRates = $axesRates->get($axis->getCode()) ?? new ChannelLocaleRateCollection();
        $axisCriteriaEvaluations = $productCriteriaEvaluations->filterByAxis($axis);

        return new AxisEvaluation($axis->getCode(), $axisRates, $axisCriteriaEvaluations);
    }
}
