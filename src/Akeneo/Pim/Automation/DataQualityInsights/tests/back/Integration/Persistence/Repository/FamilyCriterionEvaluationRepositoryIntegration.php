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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\FamilyCriterionEvaluationRepository;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\EvaluationProvider;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class FamilyCriterionEvaluationRepositoryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_saves_a_family_criterion_evaluation()
    {
        $criterionCode = new CriterionCode('consistency_attribute_spelling');
        $familyId = new FamilyId(42);

        $newFamilyCriterionEvaluation = new FamilyCriterionEvaluation(
            $familyId,
            $criterionCode,
            new \DateTimeImmutable('2020-12-02 10:31:42'),
            EvaluationProvider::aWritableCriterionEvaluationResult(['a_channel' => ['en_US' => ['rate' => 75]]])
        );

        $this->get(FamilyCriterionEvaluationRepository::class)->save($newFamilyCriterionEvaluation);
        $this->assertFamilyCriterionEvaluationInDatabaseEquals($newFamilyCriterionEvaluation);

        $updatedFamilyCriterionEvaluation = new FamilyCriterionEvaluation(
            $familyId,
            $criterionCode,
            new \DateTimeImmutable('2020-12-03 11:31:17'),
            EvaluationProvider::aWritableCriterionEvaluationResult(['a_channel' => ['en_US' => ['rate' => 100]]])
        );

        $this->get(FamilyCriterionEvaluationRepository::class)->save($updatedFamilyCriterionEvaluation);
        $this->assertFamilyCriterionEvaluationInDatabaseEquals($updatedFamilyCriterionEvaluation);
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
        $this->assertEquals($expectedFamilyCriterionEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT), $familyCriterionEvaluation['evaluated_at']);

        $evaluationResult = json_decode($familyCriterionEvaluation['result'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals($evaluationResult, $expectedFamilyCriterionEvaluation->getResult()->toArray());
    }
}
