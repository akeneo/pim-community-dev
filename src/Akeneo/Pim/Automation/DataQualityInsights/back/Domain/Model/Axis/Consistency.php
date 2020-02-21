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

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Textarea\EvaluateUppercaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Criterion\LowerCaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class Consistency implements Axis
{
    public const AXIS_CODE = 'consistency';

    public const CRITERIA_CODES = [
        EvaluateUppercaseWords::CRITERION_CODE,
        EvaluateTitleFormatting::CRITERION_CODE,
        EvaluateSpelling::CRITERION_CODE,
        LowerCaseWords::CRITERION_CODE,
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
}
