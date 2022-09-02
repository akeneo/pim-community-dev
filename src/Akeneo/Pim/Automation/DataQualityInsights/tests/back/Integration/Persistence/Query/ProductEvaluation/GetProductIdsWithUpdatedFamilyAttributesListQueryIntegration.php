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
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductIdsWithUpdatedFamilyAttributesListQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

final class GetProductIdsWithUpdatedFamilyAttributesListQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute('name');
    }

    public function test_it_retrieves_ids_of_products_with_updated_family_attributes_since_a_given_date()
    {
        $now = new \DateTimeImmutable('2020-06-21 14:32:56');

        $this->givenAFamilyWithAttributesUpdatedSince('updated_family_1', $now);
        $this->givenAFamilyWithAttributesUpdatedSince('updated_family_2', $now);
        $this->givenAFamilyWithAttributesNotUpdatedSince('not_updated_family', $now);

        $expectedProductUuids = [];
        $expectedProductUuids[] = $this->givenProductWithoutAttributeSpellcheckEvaluatedSince('updated_family_1', $now);
        $expectedProductUuids[] = $this->givenProductWithoutAttributeSpellcheckEvaluatedSince('updated_family_1', $now);
        $expectedProductUuids[] = $this->givenProductWithoutAttributeSpellcheckEvaluatedSince('updated_family_1', $now);
        $expectedProductUuids[] = $this->givenProductWithoutAttributeSpellcheckEvaluatedSince('updated_family_2', $now);
        $this->givenProductWithAttributeSpellcheckEvaluatedSince('updated_family_1', $now);
        $this->givenProductWithPendingAttributeSpellcheckEvaluation('updated_family_2', $now);
        $this->givenProductWithoutAttributeSpellcheckEvaluatedSince('not_updated_family', $now);

        $productUuids = $this->get(GetProductIdsWithUpdatedFamilyAttributesListQuery::class)->updatedSince($now, 2);
        $productUuids = iterator_to_array($productUuids);
        $productUuids = array_map(fn(ProductUuidCollection $collection) => $collection->toArray(), $productUuids);

        $this->assertCount(2, $productUuids);
        $this->assertCount(2, $productUuids[0]);
        $this->assertCount(2, $productUuids[1]);

        $this->assertEqualsCanonicalizing($expectedProductUuids, array_merge(...$productUuids));
    }

    private function givenAFamilyWithAttributesUpdatedSince(string $code, \DateTimeImmutable $updatedSince): void
    {
        $family = $this->createFamily($code, $updatedSince->modify('-1 day'));
        $this->updateFamilyAttributes($family, ['sku', 'name'], $updatedSince->modify('+1 second'));
    }

    private function givenAFamilyWithAttributesNotUpdatedSince(string $code, \DateTimeImmutable $updatedSince): void
    {
        $family = $this->createFamily($code, $updatedSince->modify('-1 day'));
        $this->updateFamilyAttributes($family, ['sku', 'name'], $updatedSince->modify('-1 second'));
    }

    private function givenProductWithoutAttributeSpellcheckEvaluatedSince(string $familyCode, \DateTimeImmutable $updatedSince): ProductUuid
    {
        $productUuid = $this->createProduct($familyCode);
        $this->createProductAttributeSpellingEvaluation($productUuid, $updatedSince->modify('-1 second'));

        return $productUuid;
    }

    private function givenProductWithAttributeSpellcheckEvaluatedSince(string $familyCode, \DateTimeImmutable $updatedSince): void
    {
        $productUuid = $this->createProduct($familyCode);
        $this->createProductAttributeSpellingEvaluation($productUuid, $updatedSince->modify('+1 second'));
    }

    private function givenProductWithPendingAttributeSpellcheckEvaluation(string $familyCode, \DateTimeImmutable $updatedSince): void
    {
        $productUuid = $this->createProduct($familyCode);
        $this->createProductAttributeSpellingEvaluation($productUuid, $updatedSince->modify('-2 second'), true);
    }

    private function createAttribute(string $code): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(string $code, \DateTimeImmutable $createdAt): FamilyInterface
    {
        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build(['code' => $code]);
        $this->get('pim_catalog.saver.family')->save($family);
        $this->updateFamilyVersionDate($family->getId(), $createdAt);

        return $family;
    }

    private function updateFamilyAttributes(FamilyInterface $family, array $attributes, \DateTimeImmutable $updatedAt): void
    {
        $lastVersionId = $this->getLastFamilyVersionId($family->getId());

        $this->get('pim_catalog.updater.family')->update($family, ['attributes' => $attributes]);
        $this->get('pim_catalog.saver.family')->save($family);

        $this->updateFamilyVersionDate($family->getId(), $updatedAt, $lastVersionId);
    }

    private function createProduct(string $family): ProductUuid
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(Uuid::uuid4()->toString())
            ->withFamily($family)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());
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

        return intval($stmt->fetchColumn());
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

    private function createProductAttributeSpellingEvaluation(ProductUuid $productUuid, \DateTimeImmutable $evaluatedAt, bool $pending = false): void
    {
        $spellingEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE),
            $productUuid,
            CriterionEvaluationStatus::pending()
        );
        $otherEvaluation = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productUuid,
            CriterionEvaluationStatus::pending()
        );

        $repository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
        $evaluations = (new Write\CriterionEvaluationCollection())
            ->add($spellingEvaluation)
            ->add($otherEvaluation);
        $repository->create($evaluations);

        if (false === $pending) {
            $spellingEvaluation->end(new Write\CriterionEvaluationResult());
            $otherEvaluation->end(new Write\CriterionEvaluationResult());
            $repository->update($evaluations);
        }

        $this->updateProductEvaluationsAt($productUuid, EvaluateAttributeSpelling::CRITERION_CODE, $evaluatedAt);
        $this->updateProductEvaluationsAt($productUuid, 'spelling', $evaluatedAt->modify('+1 hour'));
    }

    private function updateProductEvaluationsAt(ProductUuid $productUuid, string $criterionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation e, pim_catalog_product p
SET e.evaluated_at = :evaluated_at
WHERE p.uuid = :product_uuid AND e.product_uuid = p.uuid AND criterion_code = :criterionCode;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_uuid' => $productUuid->toBytes(),
            'criterionCode' => $criterionCode,
        ]);
    }
}
