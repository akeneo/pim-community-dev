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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\FamilyCriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetFamilyAttributesCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\FamilyCriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;

final class EvaluateFamilyAttributesSpelling
{
    private EvaluateAttributeSpelling $evaluateAttributeSpelling;

    private GetFamilyAttributesCodesQueryInterface $getFamilyAttributesCodes;

    private FamilyCriterionEvaluationRepositoryInterface $familyCriterionEvaluationRepository;

    public function __construct(
        EvaluateAttributeSpelling $evaluateAttributeSpelling,
        GetFamilyAttributesCodesQueryInterface $getFamilyAttributesCodes,
        FamilyCriterionEvaluationRepositoryInterface $familyCriterionEvaluationRepository
    ) {
        $this->evaluateAttributeSpelling = $evaluateAttributeSpelling;
        $this->getFamilyAttributesCodes = $getFamilyAttributesCodes;
        $this->familyCriterionEvaluationRepository = $familyCriterionEvaluationRepository;
    }

    public function evaluate(FamilyId $familyId): void
    {
        $attributeCodes = $this->getFamilyAttributesCodes->byFamilyId($familyId);
        $evaluationResult = $this->evaluateAttributeSpelling->byAttributeCodes($attributeCodes);

        $evaluation = new FamilyCriterionEvaluation(
            $familyId,
            new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::done(),
            $evaluationResult
        );

        $this->familyCriterionEvaluationRepository->save($evaluation);
    }
}
