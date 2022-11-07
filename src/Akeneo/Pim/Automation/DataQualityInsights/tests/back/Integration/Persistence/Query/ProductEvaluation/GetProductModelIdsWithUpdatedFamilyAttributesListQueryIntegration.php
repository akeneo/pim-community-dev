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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelIdsWithUpdatedFamilyAttributesListQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

final class GetProductModelIdsWithUpdatedFamilyAttributesListQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute('name', AttributeTypes::TEXT);
        $this->createAttribute('title', AttributeTypes::TEXT);
        $this->createAttribute('axis_1', AttributeTypes::OPTION_SIMPLE_SELECT);
        $this->createAttribute('axis_2', AttributeTypes::OPTION_SIMPLE_SELECT);
    }

    public function test_it_retrieves_ids_of_product_models_with_updated_family_attributes_since_a_given_date()
    {
        $now = new \DateTimeImmutable('2020-06-21 14:32:56');

        $this->givenAFamilyWithAttributesUpdatedSince('updated_family_1', $now);
        $this->givenAFamilyWithAttributesUpdatedSince('updated_family_2', $now);
        $this->givenAFamilyWithAttributesNotUpdatedSince('not_updated_family', $now);

        $expectedProductModelIds = [];
        $expectedProductModelIds[] = $this->givenProductModelWithoutAttributeSpellcheckEvaluatedSince('product_model_1', 'updated_family_1_variant', $now);
        $expectedProductModelIds[] = $this->givenSubProductModelWithoutAttributeSpellcheckEvaluatedSince('product_model_1', 'updated_family_1_variant', $now);
        $expectedProductModelIds[] = $this->givenProductWithoutAttributeSpellcheckEvaluatedSince('product_model_2', 'updated_family_2_variant', $now);
        $this->givenProductModelWithAttributeSpellcheckEvaluatedSince('updated_family_1_variant', $now);
        $this->givenProductModelWithPendingAttributeSpellcheckEvaluation('updated_family_2_variant', $now);
        $this->givenProductModelWithoutAttributeSpellcheckEvaluatedSince('foo', 'not_updated_family_variant', $now);

        $productModelIds = $this->get(GetProductModelIdsWithUpdatedFamilyAttributesListQuery::class)->updatedSince($now, 2);
        $productModelIds = iterator_to_array($productModelIds);
        $productModelIds = array_map(fn (ProductModelIdCollection $collection) => $collection->toArray(), $productModelIds);

        $this->assertCount(2, $productModelIds);
        $this->assertCount(2, $productModelIds[0]);
        $this->assertCount(1, $productModelIds[1]);

        $this->assertEqualsCanonicalizing($expectedProductModelIds, array_merge(...$productModelIds));
    }

    private function givenAFamilyWithAttributesUpdatedSince(string $familyCode, \DateTimeImmutable $updatedSince): void
    {
        $family = $this->createFamily($familyCode, $updatedSince->modify('-1 day'));
        $this->updateFamilyAttributes($family, ['name', 'axis_1', 'axis_2'], $updatedSince->modify('+1 second'));

        $this->createFamilyVariant([
            'code' => $familyCode . '_variant',
            'family' => $familyCode,
            'variant_attribute_sets' => [
                ['axes' => ['axis_1'], 'level' => 1],
                ['axes' => ['axis_2'], 'level' => 2],
            ],
        ]);
    }

    private function givenAFamilyWithAttributesNotUpdatedSince(string $familyCode, \DateTimeImmutable $updatedSince): void
    {
        $family = $this->createFamily($familyCode, $updatedSince->modify('-1 day'));
        $this->updateFamilyAttributes($family, ['name', 'axis_1', 'axis_2'], $updatedSince->modify('-1 second'));

        $this->createFamilyVariant([
            'code' => $familyCode . '_variant',
            'family' => $familyCode,
            'variant_attribute_sets' => [
                ['axes' => ['axis_1'], 'level' => 1],
            ],
        ]);
    }

    private function givenProductModelWithoutAttributeSpellcheckEvaluatedSince(string $productModelCode, string $familyVariantCode, \DateTimeImmutable $evaluatedSince): ProductModelId
    {
        $productModelId = $this->createProductModel($productModelCode, $familyVariantCode);
        $this->createProductModelAttributeSpellingEvaluation($productModelId, $evaluatedSince->modify('-1 second'));

        return $productModelId;
    }

    private function givenSubProductModelWithoutAttributeSpellcheckEvaluatedSince(string $parentCode, string $familyVariantCode, \DateTimeImmutable $evaluatedSince): ProductModelId
    {
        $subProductModelId = $this->createSubProductModel($parentCode, $familyVariantCode);
        $this->createProductModelAttributeSpellingEvaluation($subProductModelId, $evaluatedSince->modify('-1 second'));

        return $subProductModelId;
    }

    private function givenProductWithoutAttributeSpellcheckEvaluatedSince(string $productModelCode, string $familyVariantCode, \DateTimeImmutable $evaluatedSince): ProductModelId
    {
        $productModelId = $this->createProductModel($productModelCode, $familyVariantCode);
        $this->createProductModelAttributeSpellingEvaluation($productModelId, $evaluatedSince->modify('-1 second'));

        return $productModelId;
    }

    private function givenProductModelWithAttributeSpellcheckEvaluatedSince(string $familyVariantCode, \DateTimeImmutable $evaluatedSince): ProductModelId
    {
        $productModelId = $this->createProductModel(strval(Uuid::uuid4()), $familyVariantCode);
        $this->createProductModelAttributeSpellingEvaluation($productModelId, $evaluatedSince->modify('+1 second'));

        return $productModelId;
    }

    private function givenProductModelWithPendingAttributeSpellcheckEvaluation(string $familyVariantCode, \DateTimeImmutable $evaluatedSince): ProductModelId
    {
        $productModelId = $this->createProductModel(strval(Uuid::uuid4()), $familyVariantCode);
        $this->createProductModelAttributeSpellingEvaluation($productModelId, $evaluatedSince->modify('-2 second'), true);

        return $productModelId;
    }

    private function createProductModel(string $code, string $familyVariant): ProductModelId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $this->get(ProductModelIdFactory::class)->create((string)$productModel->getId());
    }

    private function createSubProductModel(string $parent, string $familyVariant): ProductModelId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant($familyVariant)
            ->withParent($parent)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $this->get(ProductModelIdFactory::class)->create((string)$productModel->getId());
    }

    private function createAttribute(string $code, string $type): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => $type,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(string $code, \DateTimeImmutable $createdAt): FamilyInterface
    {
        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => $code,
                'attributes' => ['name', 'title', 'axis_1', 'axis_2']
            ]);
        $this->get('pim_catalog.saver.family')->save($family);
        $this->updateFamilyVersionDate($family->getId(), $createdAt);

        return $family;
    }

    private function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);

        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    private function updateFamilyAttributes(FamilyInterface $family, array $attributes, \DateTimeImmutable $updatedAt): void
    {
        $lastVersionId = $this->getLastFamilyVersionId($family->getId());

        $this->get('pim_catalog.updater.family')->update($family, ['attributes' => $attributes]);
        $this->get('pim_catalog.saver.family')->save($family);

        $this->updateFamilyVersionDate($family->getId(), $updatedAt, $lastVersionId);
    }

    private function getLastFamilyVersionId(int $familyId): int
    {
        $query = <<<SQL
SELECT MAX(id) FROM pim_versioning_version
WHERE resource_name = :familyClass AND resource_id = :familyId
SQL;

        $stmt = $this->get('database_connection')->executeQuery($query, [
            'familyClass' => $this->getParameter('pim_catalog.entity.family.class'),
            'familyId' => $familyId,
        ]);

        return intval($stmt->fetchOne());
    }

    private function updateFamilyVersionDate(int $familyId, \DateTimeImmutable $updatedAt, ?int $lastVersionId = null): void
    {
        $query = <<<SQL
UPDATE pim_versioning_version SET logged_at = :updatedAt
WHERE resource_name = :familyClass AND resource_id = :familyId AND id > :lastId
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'updatedAt' => $updatedAt->format(Clock::TIME_FORMAT),
            'familyClass' => $this->getParameter('pim_catalog.entity.family.class'),
            'familyId' => $familyId,
            'lastId' => $lastVersionId ?? 0,
        ]);
    }

    private function createProductModelAttributeSpellingEvaluation(ProductModelId $productModelId, \DateTimeImmutable $evaluatedAt, bool $pending = false): void
    {
        $spellingEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE),
            $productModelId,
            CriterionEvaluationStatus::pending()
        );
        $otherEvaluation = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productModelId,
            CriterionEvaluationStatus::pending()
        );

        $repository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');
        $evaluations = (new Write\CriterionEvaluationCollection())
            ->add($spellingEvaluation)
            ->add($otherEvaluation);
        $repository->create($evaluations);

        if (false === $pending) {
            $spellingEvaluation->end(new Write\CriterionEvaluationResult());
            $otherEvaluation->end(new Write\CriterionEvaluationResult());
            $repository->update($evaluations);
        }

        $this->updateProductModelEvaluationsAt($productModelId, EvaluateAttributeSpelling::CRITERION_CODE, $evaluatedAt);
        $this->updateProductModelEvaluationsAt($productModelId, 'spelling', $evaluatedAt->modify('+1 hour'));
    }

    private function updateProductModelEvaluationsAt(ProductModelId $productModelId, string $criterionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation
SET evaluated_at = :evaluated_at
WHERE product_id = :product_id AND criterion_code = :criterionCode;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productModelId,
            'criterionCode' => $criterionCode,
        ]);
    }
}
