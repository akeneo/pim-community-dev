<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductIdsImpactedByAttributeGroupActivationQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdsImpactedByAttributeGroupActivationQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_retrieves_products_impacted_by_attribute_group_activation_updated_since_a_given_date()
    {
        $updatedSince = new \DateTimeImmutable('2020-10-05 14:35:42');

        $this->createAttributeGroupActivation('other', false, $updatedSince->modify('-1 day'));
        $this->createAttributeGroupWithAttributes('not_recently_activated', ['name', 'description'], true, $updatedSince->modify('-1 month'));
        $this->createAttributeGroupWithAttributes('recently_deactivated', ['ean', 'uuid'], false, $updatedSince->modify('+1 second'));
        $this->createAttributeGroupWithAttributes('recently_activated', ['brand'], true, $updatedSince->modify('+1 minute'));

        $this->createFamily('not_impacted_family', ['attributes' => ['name', 'description']]);
        $this->createFamily('impacted_family_A', ['attributes' => ['name', 'ean']]);
        $this->createFamily('impacted_family_B', ['attributes' => ['brand', 'uuid']]);

        $expectedProductIds[] = $this->createProductEvaluatedAt('expected_product_A', ['family' => 'impacted_family_A'], $updatedSince->modify('-1 second'));
        $expectedProductIds[] = $this->createProductEvaluatedAt('expected_product_B', ['family' => 'impacted_family_B'], $updatedSince->modify('-1 minute'));
        $expectedProductIds[] = $this->createProductEvaluatedAt('expected_product_C', ['family' => 'impacted_family_B'], $updatedSince->modify('-1 hour'));

        $this->createProductEvaluatedAt('impacted_but_already_evaluated_product', ['family' => 'impacted_family_A'], $updatedSince->modify('+1 second'));
        $this->createProductEvaluatedAt('not_impacted_product', ['family' => 'not_impacted_family'], $updatedSince->modify('-1 hour'));
        $this->createProductWithPendingEvaluations('impacted_product_but_pending_evaluation', ['family' => 'impacted_family_B'], $updatedSince->modify('-1 minute'));

        $productIds = $this->get(GetProductIdsImpactedByAttributeGroupActivationQuery::class)->updatedSince($updatedSince, 2);
        $productIds = iterator_to_array($productIds);

        $this->assertCount(2, $productIds);
        $this->assertCount(2, $productIds[0]);
        $this->assertCount(1, $productIds[1]);

        $this->assertEqualsCanonicalizing($expectedProductIds, array_merge($productIds[0], $productIds[1]));
    }

    private function createAttributeGroupWithAttributes(string $code, array $attributes, bool $activated, \DateTimeImmutable $activationUpdatedAt): int
    {
        foreach ($attributes as $attributeCode) {
            $this->createAttribute($attributeCode);
        }

        $attributeGroup = $this->createAttributeGroup($code, ['attributes' => $attributes]);

        $this->createAttributeGroupActivation($code, $activated, $activationUpdatedAt);

        return $attributeGroup->getId();
    }

    private function createProductEvaluatedAt(string $identifier, array $data, \DateTimeImmutable $evaluatedAt): ProductId
    {
        $product = $this->createProduct($identifier, $data);
        $this->updateProductEvaluationsAt($product->getId(), CriterionEvaluationStatus::DONE, $evaluatedAt);

        return new ProductId($product->getId());
    }

    private function createProductWithPendingEvaluations(string $identifier, array $data, \DateTimeImmutable $evaluatedAt): ProductId
    {
        $product = $this->createProduct($identifier, $data);
        $this->updateProductEvaluationsAt($product->getId(), CriterionEvaluationStatus::PENDING, $evaluatedAt);

        return new ProductId($product->getId());
    }
}
