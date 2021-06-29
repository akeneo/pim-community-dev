<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SynchronousCriterionEvaluationsFilter implements SynchronousCriterionEvaluationsFilterInterface
{
    private const SYNCHRONOUS_CRITERION_CODES = [
        EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
        EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
        EvaluateImageEnrichment::CRITERION_CODE,
    ];

    public function filter(\Iterator $iterator): array
    {
        return array_filter(iterator_to_array($iterator), function (CriterionEvaluation $criterionEvaluation) {
            return in_array(strval($criterionEvaluation->getCriterionCode()), self::SYNCHRONOUS_CRITERION_CODES);
        });
    }
}
