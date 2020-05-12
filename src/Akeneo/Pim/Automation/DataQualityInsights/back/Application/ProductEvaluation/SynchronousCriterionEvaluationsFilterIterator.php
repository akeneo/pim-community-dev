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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateUppercaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Criterion\LowerCaseWords;

final class SynchronousCriterionEvaluationsFilterIterator extends \FilterIterator
{
    private const SYNCHRONOUS_CRITERION_CODES = [
        EvaluateUppercaseWords::CRITERION_CODE,
        LowerCaseWords::CRITERION_CODE,
        EvaluateSpelling::CRITERION_CODE,
        EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
        EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
    ];

    public function accept()
    {
        $criterionEvaluation = $this->getInnerIterator()->current();

        return in_array(strval($criterionEvaluation->getCriterionCode()), self::SYNCHRONOUS_CRITERION_CODES);
    }
}
