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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;

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
