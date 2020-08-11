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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeOptionSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateUppercaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Criterion\LowerCaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class Consistency implements Axis
{
    public const AXIS_CODE = 'consistency';

    public const CRITERIA_CODES = [
        EvaluateSpelling::CRITERION_CODE,
        LowerCaseWords::CRITERION_CODE,
        EvaluateUppercaseWords::CRITERION_CODE,
        EvaluateAttributeSpelling::CRITERION_CODE,
        EvaluateAttributeOptionSpelling::CRITERION_CODE,
    ];

    private const CRITERIA_COEFFICIENTS = [
        EvaluateUppercaseWords::CRITERION_CODE => 1,
        EvaluateSpelling::CRITERION_CODE => 2,
        LowerCaseWords::CRITERION_CODE => 1,
        EvaluateAttributeSpelling::CRITERION_CODE => 1,
        EvaluateAttributeOptionSpelling::CRITERION_CODE => 1,
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
