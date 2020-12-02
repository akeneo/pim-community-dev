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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\FamilyCriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Doctrine\DBAL\Connection;

final class FamilyCriterionEvaluationRepository implements FamilyCriterionEvaluationRepositoryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function save(Write\FamilyCriterionEvaluation $familyCriterionEvaluation): void
    {
        $query = <<<SQL
UPDATE pimee_dqi_family_criteria_evaluation 
SET status = :status, evaluated_at = :evaluatedAt, result = :result
WHERE family_id = :familyId AND criterion_code = :criterionCode;
SQL;
        $this->dbConnection->executeQuery(
            $query,
            [
                'familyId' => $familyCriterionEvaluation->getFamilyId()->toInt(),
                'criterionCode' => $familyCriterionEvaluation->getCriterionCode(),
                'status' => $familyCriterionEvaluation->getStatus(),
                'evaluatedAt' => $this->formatEvaluatedAt($familyCriterionEvaluation->getEvaluatedAt()),
                'result' => $this->formatEvaluationResult($familyCriterionEvaluation->getResult()),
            ],
            [
                'familyId' => \PDO::PARAM_INT
            ]
        );
    }

    public function setToPending(FamilyId $familyId, CriterionCode $criterionCode): void
    {
        $query = <<<SQL
INSERT INTO pimee_dqi_family_criteria_evaluation (family_id, criterion_code, status)
VALUES (:familyId, :criterionCode, :status)
ON DUPLICATE KEY UPDATE status = :status;
SQL;
        $this->dbConnection->executeQuery(
            $query,
            [
                'familyId' => $familyId->toInt(),
                'criterionCode' => $criterionCode,
                'status' => CriterionEvaluationStatus::PENDING,
            ],
            [
                'familyId' => \PDO::PARAM_INT
            ]
        );
    }

    private function formatEvaluatedAt(?\DateTimeImmutable $evaluatedAt): ?string
    {
        return null !== $evaluatedAt ? $evaluatedAt->format(Clock::TIME_FORMAT) : null;
    }

    private function formatEvaluationResult(?Write\CriterionEvaluationResult $criterionEvaluationResult): ?string
    {
        return null !== $criterionEvaluationResult ? json_encode($criterionEvaluationResult->toArray()) : null;
    }
}
