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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class Enrichment implements Axis
{
    public const AXIS_CODE = 'enrichment';

    public const CRITERIA_CODES = [
        EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
        EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
        EvaluateImageEnrichment::CRITERION_CODE
    ];

    public const CRITERIA_COEFFICIENTS = [
        EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE => 2,
        EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE => 1,
        EvaluateImageEnrichment::CRITERION_CODE => 2,
    ];

    /** @var AxisCode */
    private $code;

    /** @var CriterionCode[] */
    private $criteriaCodes;

    public function __construct()
    {
        $this->code = new AxisCode(self::AXIS_CODE);
        $this->criteriaCodes = array_map(function ($criterion) {
            return new CriterionCode($criterion);
        }, self::CRITERIA_CODES);
    }

    public function getCode(): AxisCode
    {
        return $this->code;
    }

    public function getCriteriaCodes(): array
    {
        return $this->criteriaCodes;
    }

    public function getCriterionCoefficient(CriterionCode $criterionCode): int
    {
        return self::CRITERIA_COEFFICIENTS[strval($criterionCode)] ?? 1;
    }
}
