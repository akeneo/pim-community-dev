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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;

final class FamilyCriterionEvaluation
{
    private FamilyId $familyId;

    private CriterionCode $criterionCode;

    private \DateTimeImmutable $evaluatedAt;

    private CriterionEvaluationResult $result;

    public function __construct(FamilyId $familyId, CriterionCode $criterionCode, \DateTimeImmutable $evaluatedAt, CriterionEvaluationResult $result)
    {
        $this->familyId = $familyId;
        $this->criterionCode = $criterionCode;
        $this->evaluatedAt = $evaluatedAt;
        $this->result = $result;
    }

    public function getFamilyId(): FamilyId
    {
        return $this->familyId;
    }

    public function getCriterionCode(): CriterionCode
    {
        return $this->criterionCode;
    }

    public function getEvaluatedAt(): \DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function getResult(): CriterionEvaluationResult
    {
        return $this->result;
    }
}
