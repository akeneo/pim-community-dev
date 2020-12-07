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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetFamilyIdsToEvaluateQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class GetFamilyIdsToEvaluateQueryIntegration extends DataQualityInsightsTestCase
{
    /** @var \DateTimeImmutable $referenceDate */
    private $referenceDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceDate = new \DateTimeImmutable('2020-12-02 16:01:34');
    }

    public function test_it_retrieves_the_ids_of_the_families_to_evaluate()
    {
        $this->givenAttributes();

        $expectedFamilyIds = [];
        $expectedFamilyIds[] = $this->givenAFamilyWithoutEvaluation();
        $expectedFamilyIds[] = $this->givenAFamilyWithOutdatedEvaluation();
        $expectedFamilyIds[] = $this->givenAFamilyWithAnYoungerAttributeSpellcheck();

        $this->givenAFamilyWithAnUpToDateEvaluation();
        $this->givenAFamilyWithAYoungerButDeactivatedAttribute();

        $familyIdsToEvaluate = iterator_to_array(
            $this->get(GetFamilyIdsToEvaluateQuery::class)->execute(2)
        );

        $this->assertCount(2, $familyIdsToEvaluate);
        $familyIdsToEvaluate = array_merge(...$familyIdsToEvaluate);

        $this->assertEqualsCanonicalizing($expectedFamilyIds, $familyIdsToEvaluate);
    }

    private function givenAttributes(): void
    {
        $this->createAttributeGroup('activated_group');
        $this->createAttributeGroup('deactivated_group');

        $this->createAttribute('attribute_up_to_date_spellcheck', ['group' => 'activated_group']);
        $this->createAttribute('attribute_younger_spellcheck', ['group' => 'activated_group']);
        $this->createAttribute('deactivated_attribute_younger_spellcheck', ['group' => 'deactivated_group']);

        $this->updateAttributeSpellcheck('attribute_up_to_date_spellcheck', $this->referenceDate->modify('-1 second'));
        $this->updateAttributeSpellcheck('attribute_younger_spellcheck', $this->referenceDate->modify('+1 second'));
        $this->updateAttributeSpellcheck('deactivated_attribute_younger_spellcheck', $this->referenceDate->modify('+1 hour'));
    }

    private function givenAFamilyWithoutEvaluation(): FamilyId
    {
        $family = $this->createFamily('family_without_evaluation');
        $familyId = new FamilyId($family->getId());

        $this->updateFamilyAtReferenceDate($familyId);

        $query = <<<SQL
DELETE FROM pimee_dqi_family_criteria_evaluation WHERE family_id = :familyId;
SQL;
        $this->get('database_connection')->executeQuery($query, ['familyId' => $family->getId()]);

        return $familyId;
    }

    private function givenAFamilyWithOutdatedEvaluation(): FamilyId
    {
        $family = $this->createFamily('family_with_outdated_evaluation', ['attributes' => ['attribute_up_to_date_spellcheck']]);
        $familyId = new FamilyId($family->getId());

        $this->updateFamilyAtReferenceDate($familyId);
        $this->updateFamilyEvaluation($familyId, $this->referenceDate->modify('-1 second'));

        return $familyId;
    }

    private function givenAFamilyWithAnYoungerAttributeSpellcheck(): FamilyId
    {
        $family = $this->createFamily('family_with_a_younger_attribute_spellcheck', [
            'attributes' => ['attribute_up_to_date_spellcheck', 'attribute_younger_spellcheck']
        ]);
        $familyId = new FamilyId($family->getId());

        $this->updateFamilyAtReferenceDate($familyId);
        $this->updateFamilyEvaluation($familyId, $this->referenceDate);

        return $familyId;
    }

    private function givenAFamilyWithAnUpToDateEvaluation(): void
    {
        $family = $this->createFamily('family_with_up_to_date_evaluation', ['attributes' => ['attribute_up_to_date_spellcheck']]);
        $familyId = new FamilyId($family->getId());

        $this->updateFamilyAtReferenceDate($familyId);
        $this->updateFamilyEvaluation($familyId, $this->referenceDate->modify('+1 day'));
    }

    private function givenAFamilyWithAYoungerButDeactivatedAttribute(): void
    {
        $family = $this->createFamily('family_with_outdated_but_deactivated_attribute', [
            'attributes' => ['deactivated_attribute_younger_spellcheck']
        ]);
        $familyId = new FamilyId($family->getId());

        $this->updateFamilyAtReferenceDate($familyId);
        $this->updateFamilyEvaluation($familyId, $this->referenceDate->modify('+1 day'));
    }

    private function updateFamilyAtReferenceDate(FamilyId $familyId): void
    {
        $query = <<<SQL
UPDATE pim_catalog_family SET updated = :updatedAt WHERE id = :familyId;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'familyId' => $familyId->toInt(),
            'updatedAt' => $this->referenceDate->format(Clock::TIME_FORMAT)
        ]);
    }

    private function updateFamilyEvaluation(FamilyId $familyId, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
INSERT INTO pimee_dqi_family_criteria_evaluation (family_id, criterion_code, evaluated_at)
VALUES (:familyId, 'consistency_attribute_spelling', :evaluatedAt)
ON DUPLICATE KEY UPDATE evaluated_at = :evaluatedAt;
SQL;
        $this->get('database_connection')->executeQuery($query, [
            'familyId' => $familyId->toInt(),
            'evaluatedAt' => $evaluatedAt->format(Clock::TIME_FORMAT)
        ]);
    }

    private function updateAttributeSpellcheck(string $attributeCode, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
INSERT INTO pimee_dqi_attribute_spellcheck (attribute_code, evaluated_at, to_improve, result) 
VALUES (:attributeCode, :evaluatedAt, 0, '{}')
ON DUPLICATE KEY UPDATE evaluated_at = :evaluatedAt;
SQL;
        $this->get('database_connection')->executeQuery($query, [
            'evaluatedAt' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'attributeCode' => $attributeCode
        ]);
    }
}
