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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\FamilyCriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\FamilyCriterionEvaluationRepository;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\EvaluationProvider;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class FamilyCriterionEvaluationRepositoryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_saves_a_family_criterion_evaluation()
    {
        $criterionCode = new CriterionCode('consistency_attribute_spelling');
        $familyIdToSave = new FamilyId(42);
        $anotherFamilyId = new FamilyId(123);

        $this->givenAFamilyCriterionEvaluationToPending($familyIdToSave, $criterionCode);
        $this->givenAFamilyCriterionEvaluationToPending($anotherFamilyId, $criterionCode);

        $familyCriterionEvaluationToSave = new FamilyCriterionEvaluation(
            $familyIdToSave,
            new CriterionCode('consistency_attribute_spelling'),
            new \DateTimeImmutable('2020-12-02 10:31:42'),
            CriterionEvaluationStatus::done(),
            EvaluationProvider::aWritableCriterionEvaluationResult()
        );

        $this->get(FamilyCriterionEvaluationRepository::class)->save($familyCriterionEvaluationToSave);

        $this->assertFamilyCriterionEvaluationInDatabaseEquals($familyCriterionEvaluationToSave);
        $this->assertFamilyCriterionEvaluationIsPending($anotherFamilyId, $criterionCode);
    }

    public function test_it_sets_a_family_criterion_evaluation_to_pending()
    {
        $criterionCode = new CriterionCode('consistency_attribute_spelling');
        $familyIdToSetToPending = new FamilyId(42);
        $anotherFamilyId = new FamilyId(123);

        $this->givenAFamilyCriterionEvaluationDone($familyIdToSetToPending, $criterionCode);
        $anotherFamilyCriterionEvaluation = $this->givenAFamilyCriterionEvaluationDone($anotherFamilyId, $criterionCode);

        $this->get(FamilyCriterionEvaluationRepository::class)->setToPending($familyIdToSetToPending, $criterionCode);

        $this->assertFamilyCriterionEvaluationIsPending($familyIdToSetToPending, $criterionCode);
        $this->assertFamilyCriterionEvaluationInDatabaseEquals($anotherFamilyCriterionEvaluation);
    }

    private function givenAFamilyCriterionEvaluationToPending(FamilyId $familyId, CriterionCode $criterionCode): void
    {
        $query = <<<SQL
INSERT INTO pimee_dqi_family_criteria_evaluation (family_id, criterion_code, status)
VALUES (:familyId, :criterionCode, 'pending');
SQL;
        $this->get('database_connection')->executeQuery(
            $query,
            ['familyId' => $familyId->toInt(), 'criterionCode' => $criterionCode],
            ['familyId' => \PDO::PARAM_INT]
        );
    }

    private function givenAFamilyCriterionEvaluationDone(FamilyId $familyId, CriterionCode $criterionCode): FamilyCriterionEvaluation
    {
        $this->givenAFamilyCriterionEvaluationToPending($familyId, $criterionCode);

        $familyCriterionEvaluation = new FamilyCriterionEvaluation(
            $familyId,
            $criterionCode,
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::done(),
            EvaluationProvider::aWritableCriterionEvaluationResult()
        );

        $this->get(FamilyCriterionEvaluationRepository::class)->save($familyCriterionEvaluation);

        return $familyCriterionEvaluation;
    }

    private function assertFamilyCriterionEvaluationInDatabaseEquals(FamilyCriterionEvaluation $expectedFamilyCriterionEvaluation): void
    {
        $query = <<<SQL
SELECT * FROM pimee_dqi_family_criteria_evaluation WHERE family_id = :familyId
SQL;

        $familyCriterionEvaluation = $this->get('database_connection')->executeQuery(
            $query, ['familyId' => $expectedFamilyCriterionEvaluation->getFamilyId()->toInt()], ['familyId' => \PDO::PARAM_INT]
        )->fetch(\PDO::FETCH_ASSOC);

        $this->assertIsArray($familyCriterionEvaluation);
        $this->assertEquals($expectedFamilyCriterionEvaluation->getCriterionCode(), $familyCriterionEvaluation['criterion_code']);
        $this->assertEquals($expectedFamilyCriterionEvaluation->getStatus(), $familyCriterionEvaluation['status']);
        $this->assertEquals($expectedFamilyCriterionEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT), $familyCriterionEvaluation['evaluated_at']);

        $evaluationResult = json_decode($familyCriterionEvaluation['result'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals($evaluationResult, $expectedFamilyCriterionEvaluation->getResult()->toArray());
    }

    private function assertFamilyCriterionEvaluationIsPending(FamilyId $familyId, CriterionCode $criterionCode): void
    {
        $query = <<<SQL
SELECT 1 FROM pimee_dqi_family_criteria_evaluation WHERE
family_id = :familyId AND criterion_code = :criterionCode AND status = 'pending'
SQL;

        $familyCriterionEvaluationIsPending = (bool) $this->get('database_connection')->executeQuery(
            $query,
            ['familyId' => $familyId->toInt(), 'criterionCode' => $criterionCode],
            ['familyId' => \PDO::PARAM_INT]
        )->fetchColumn();

        $this->assertTrue($familyCriterionEvaluationIsPending);
    }
}
