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

namespace Akeneo\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelIdsWithOutdatedAttributeSpellcheckQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

final class GetProductModelIdsWithOutdatedAttributeSpellcheckQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_retrieves_the_product_models_with_outdated_attribute_spellchecks()
    {
        $spellcheckEvaluatedSince = new \DateTimeImmutable('2020-06-11 11:09:42');

        $this->createAttributeSpellcheck('a_metric', $spellcheckEvaluatedSince->modify('+1 second'));

        // To ensure that product models with up-to-date attribute spellcheck are excluded
        $this->createProductModelEvaluations(
            $this->createProductModel('uptodate_product_model', 'familyVariantA2'),
            $spellcheckEvaluatedSince->modify('+1 minute')
        );

        // To ensure that product models with pending attribute spelling evaluation are excluded
        $productModelId = $this->createProductModel('pm_pending_evaluation', 'familyVariantA1');
        $this->createProductModelEvaluations($productModelId, $spellcheckEvaluatedSince, true);

        $productModelA1 = $this->createProductModel('impacted_parent_A1', 'familyVariantA1');
        $this->createProductModelEvaluations($productModelA1, $spellcheckEvaluatedSince);

        $subProductModelA2 = $this->createSubProductModel('impacted_parent_A1', 'familyVariantA1');
        $this->createProductModelEvaluations($subProductModelA2, $spellcheckEvaluatedSince->modify('-1 second'));

        $productModelA2 = $this->createProductModel('impacted_parent_A2', 'familyVariantA2');
        $this->createProductModelEvaluations($productModelA2, $spellcheckEvaluatedSince);

        // To ensure that product models without attribute spelling evaluation are taken
        $productModelA3 = $this->createProductModel('pm_without_evaluations', 'familyVariantA1');
        $this->deleteProductModelEvaluations($productModelA3);

        $expectedProductModelsIds = $this->get(ProductModelIdFactory::class)->createCollection([
            (string)$productModelA1,
            (string)$subProductModelA2,
            (string)$productModelA2,
            (string)$productModelA3,
        ])->toArray();

        $productsModelIds = $this->get(GetProductModelIdsWithOutdatedAttributeSpellcheckQuery::class)
            ->evaluatedSince($spellcheckEvaluatedSince, 3);
        $productsModelIds = iterator_to_array($productsModelIds);
        $productsModelIds = array_map(fn(ProductModelIdCollection $collection) => $collection->toArray(), $productsModelIds);

        $this->assertCount(2, $productsModelIds);
        $this->assertEqualsCanonicalizing($expectedProductModelsIds, array_merge(...$productsModelIds));
    }

    public function test_it_returns_only_product_models_impacted_by_attributes_of_their_level()
    {
        $spellcheckEvaluatedSince = new \DateTimeImmutable('2020-06-11 11:09:42');

        $this->createAttributeSpellcheck('a_yes_no', $spellcheckEvaluatedSince->modify('+2 second'));
        // To ensure that "too old" attribute spellchecks are excluded
        $this->createAttributeSpellcheck('a_text_area', $spellcheckEvaluatedSince->modify('-1 second'));

        // To ensure that product models are excluded if the attribute belongs to product variants
        $parentProductModelId = $this->createProductModel('parent_product_model_A1', 'familyVariantA1');
        $this->createProductModelEvaluations($parentProductModelId, $spellcheckEvaluatedSince);
        $subProductModelId = $this->createSubProductModel('parent_product_model_A1', 'familyVariantA1');
        $this->createProductModelEvaluations($subProductModelId, $spellcheckEvaluatedSince);

        // To ensure that product models are excluded if the attribute belongs to sub product models
        $parentProductModelA2 = $this->createProductModel('parent_product_model_A2', 'familyVariantA2');
        $this->createProductModelEvaluations($parentProductModelA2, $spellcheckEvaluatedSince);
        $subProductModelA2 = $this->createSubProductModel('parent_product_model_A2', 'familyVariantA2');
        $this->createProductModelEvaluations($subProductModelA2, $spellcheckEvaluatedSince);

        $productsModelIds = $this->get(GetProductModelIdsWithOutdatedAttributeSpellcheckQuery::class)
            ->evaluatedSince($spellcheckEvaluatedSince, 2);
        $productsModelIds = iterator_to_array($productsModelIds);

        $expectedProductModelsIds = $this->get(ProductModelIdFactory::class)->createCollection([
            (string)$subProductModelA2
        ])->toArray();

        $this->assertCount(1, $productsModelIds);
        $this->assertEqualsCanonicalizing($expectedProductModelsIds, $productsModelIds[0]->toArray());
    }

    private function createProductModel(string $identifier, string $familyVariant): int
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($identifier)
            ->withFamilyVariant($familyVariant)
            ->build();
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return intval($productModel->getId());
    }

    private function createSubProductModel(string $parent, string $familyVariant): int
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withParent($parent)
            ->withFamilyVariant($familyVariant)
            ->build();
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return intval($productModel->getId());
    }

    private function createAttributeSpellcheck(string $attributeCode, \DateTimeImmutable $evaluatedAt): void
    {
        $this->get(AttributeSpellcheckRepository::class)->save(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            $evaluatedAt,
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
        ));
    }

    private function createProductModelEvaluations(int $productModelId, \DateTimeImmutable $evaluatedAt, bool $pending = false): void
    {
        $spellingEvaluation = new CriterionEvaluation(
            new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE),
            new ProductModelId($productModelId),
            CriterionEvaluationStatus::pending()
        );
        $otherEvaluation = new CriterionEvaluation(
            new CriterionCode('spelling'),
            new ProductModelId($productModelId),
            CriterionEvaluationStatus::pending()
        );

        $repository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');
        $evaluations = (new CriterionEvaluationCollection())
            ->add($spellingEvaluation)
            ->add($otherEvaluation);
        $repository->create($evaluations);

        if (false === $pending) {
            $spellingEvaluation->end(new CriterionEvaluationResult());
            $otherEvaluation->end(new CriterionEvaluationResult());
            $repository->update($evaluations);
        }

        $this->updateProductModelEvaluationsAt($productModelId, EvaluateAttributeSpelling::CRITERION_CODE, $evaluatedAt);
        $this->updateProductModelEvaluationsAt($productModelId, 'spelling', $evaluatedAt->modify('-1 hour'));
    }

    private function updateProductModelEvaluationsAt(int $productModelId, string $criterionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation
SET evaluated_at = :evaluatedAt
WHERE product_id = :productModelId AND criterion_code = :criterionCode;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'evaluatedAt' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'productModelId' => $productModelId,
            'criterionCode' => $criterionCode,
        ]);
    }

    private function deleteProductModelEvaluations(int $productModelId)
    {
        $query = <<<SQL
DELETE FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id = :productModelId
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'productModelId' => $productModelId,
        ]);
    }
}
