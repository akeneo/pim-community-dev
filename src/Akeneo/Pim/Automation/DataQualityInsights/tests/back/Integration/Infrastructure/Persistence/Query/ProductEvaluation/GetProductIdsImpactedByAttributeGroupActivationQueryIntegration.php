<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
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

        $expectedProducts[] = $this->createProduct('expected_product_A', ['family' => 'impacted_family_A']);
        $expectedProducts[] = $this->createProduct('expected_product_B', ['family' => 'impacted_family_B']);
        $expectedProducts[] = $this->createProduct('expected_product_C', ['family' => 'impacted_family_B']);

        $expectedProductIds = array_map(function ($product) {
            return $this->get(ProductIdFactory::class)->create((string)$product->getId());
        }, $expectedProducts);

        $this->createProduct('not_impacted_product', ['family' => 'not_impacted_family']);

        $productIds = $this->get(GetProductIdsImpactedByAttributeGroupActivationQuery::class)->updatedSince($updatedSince, 2);
        $productIds = iterator_to_array($productIds);
        $this->assertCount(2, $productIds);
        $this->assertCount(2, $productIds[0]);
        $this->assertCount(1, $productIds[1]);

        $productIds = array_map(fn(ProductIdCollection $collection) => $collection->toArray(), $productIds);
        $this->assertEqualsCanonicalizing($expectedProductIds, array_merge_recursive(...$productIds));
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
}
