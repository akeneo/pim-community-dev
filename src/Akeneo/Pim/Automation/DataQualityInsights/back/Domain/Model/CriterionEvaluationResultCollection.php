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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class CriterionEvaluationResultCollection
{
    /** @var CriterionEvaluationResult[] */
    private $evaluationResults;

    public function add(CriterionCode $criterionCode, CriterionEvaluationResult $criterionEvaluationResult): void
    {
        $this->evaluationResults[strval($criterionCode)] = $criterionEvaluationResult;
    }

    public function get(CriterionCode $criterionCode): ?CriterionEvaluationResult
    {
        return $this->evaluationResults[strval($criterionCode)] ?? null;
    }
}
