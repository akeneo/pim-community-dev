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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Axis;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeAxisRatesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class ComputeEnrichmentRates implements ComputeAxisRatesInterface
{
    public function compute(CriterionEvaluationCollection $criterionEvaluationCollection): AxisRateCollection
    {
        $evaluations = [
            $criterionEvaluationCollection->get(new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)),
            $criterionEvaluationCollection->get(new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE))
        ];

        $axisRateCollection = new AxisRateCollection();
        foreach ($evaluations as $evaluation) {
            if ($evaluation !== null && $evaluation->getResult() !== null) {
                $axisRateCollection->addCriterionRateCollection($evaluation->getResult()->getRates());
            }
        }

        return $axisRateCollection;
    }
}
