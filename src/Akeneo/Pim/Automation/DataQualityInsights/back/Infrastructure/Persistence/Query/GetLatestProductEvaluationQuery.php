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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Consistency;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class GetLatestProductEvaluationQuery implements GetLatestProductEvaluationQueryInterface
{
    /** @var GetLatestCriteriaEvaluationsByProductIdQueryInterface */
    private $getLatestCriteriaEvaluationsByProductIdQuery;

    /** @var GetLatestProductAxesRatesQueryInterface */
    private $getLatestProductAxesRatesQuery;

    public function __construct(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery
    ) {
        $this->getLatestCriteriaEvaluationsByProductIdQuery = $getLatestCriteriaEvaluationsByProductIdQuery;
        $this->getLatestProductAxesRatesQuery = $getLatestProductAxesRatesQuery;
    }

    public function execute(ProductId $productId): ProductEvaluation
    {
        $productAxesRates = $this->getLatestProductAxesRatesQuery->byProductId($productId);
        $productCriteriaEvaluations = $this->getLatestCriteriaEvaluationsByProductIdQuery->execute($productId);

        $axesEvaluations = new AxisEvaluationCollection();
        $axes = $this->getAxes();
        foreach ($axes as $axis) {
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

    // @fixme How and where determine the list of the axes?
    private function getAxes(): array
    {
        return [
            new Enrichment(),
            new Consistency(),
        ];
    }
}
