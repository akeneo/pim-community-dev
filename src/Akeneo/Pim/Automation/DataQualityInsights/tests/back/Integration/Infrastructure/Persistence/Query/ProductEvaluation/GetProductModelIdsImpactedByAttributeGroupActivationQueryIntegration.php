<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelIdsImpactedByAttributeGroupActivationQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelIdsImpactedByAttributeGroupActivationQueryIntegration extends DataQualityInsightsTestCase
{
    /** @var \DateTimeImmutable */
    private $updatedSince;

    protected function setUp(): void
    {
        parent::setUp();

        $this->updatedSince = new \DateTimeImmutable('2020-10-05 14:35:42');
        $this->createAttributeGroupActivation('other', false, $this->updatedSince->modify('-1 day'));
    }

    public function test_it_retrieves_product_models_impacted_by_attribute_group_activation_updated_since_a_given_date()
    {
        $this->createAttributeGroupActivation('other', false, $this->updatedSince->modify('-1 day'));
        $this->createAttributeGroupWithAttributes('not_recently_activated', ['name', 'description'], true, $this->updatedSince->modify('-1 second'));
        $this->createAttributeGroupWithAttributes('recently_activated', ['ean'], true, $this->updatedSince->modify('+1 minute'));
        $this->createAttributeGroupWithAttributes('recently_deactivated', ['weight', 'length'], false, $this->updatedSince->modify('+1 second'));
        $this->createAttributeGroupWithAttributesForVariationAxes('not_recently_deactivated', ['color', 'size'], false, $this->updatedSince->modify('-1 month'));

        $expectedProductModelIds[] = $this->givenAnImpactedRootProductModelWithASingleVariationLevel();
        $expectedProductModelIds[] = $this->givenAnImpactedRootProductModelWithTwoVariationLevels();
        $expectedProductModelIds[] = $this->givenAnotherImpactedRootProductModelWithTwoVariationLevels();
        $expectedProductModelIds[] = $this->givenASubProductModelImpactedByACommonAttribute();
        $expectedProductModelIds[] = $this->givenASubProductModelImpactedByALevelOneVariantAttribute();

        $this->givenARootProductModelNotImpactedBecauseOfItsVariantFamily();
        $this->givenARootProductModelNotImpactedBecauseOfItsLevelOneAttributes();

        $this->givenASubProductModelNotImpactedBecauseOfItsVariantFamily();
        $this->givenASubProductModelNotImpactedBecauseOfItsLevelTwoAttributes();

        $productModelIds = iterator_to_array($this->get(GetProductModelIdsImpactedByAttributeGroupActivationQuery::class)
            ->updatedSince($this->updatedSince, 2));

        $productModelIds = array_map(fn (ProductModelIdCollection $collection) => $collection->toArray(), $productModelIds);

        $this->assertCount(3, $productModelIds);
        $this->assertEqualsCanonicalizing($expectedProductModelIds, array_merge_recursive(...$productModelIds));
    }

    public function test_it_retrieves_impacted_product_models_for_a_given_attribute_group(): void
    {
        $this->createAttributeGroupActivation('other', false, $this->updatedSince->modify('-1 day'));
        $this->createAttributeGroupWithAttributes('not_recently_activated', ['name', 'description'], true, $this->updatedSince->modify('-1 second'));
        $this->createAttributeGroupWithAttributes('recently_activated', ['ean'], true, $this->updatedSince->modify('+1 minute'));
        $this->createAttributeGroupWithAttributes('recently_deactivated', ['weight', 'length'], false, $this->updatedSince->modify('+1 second'));
        $this->createAttributeGroupWithAttributesForVariationAxes('not_recently_deactivated', ['color', 'size'], false, $this->updatedSince->modify('-1 month'));

        $expectedProductModelIds[] = $this->givenAnImpactedRootProductModelWithASingleVariationLevel();
        $expectedProductModelIds[] = $this->givenAnImpactedRootProductModelWithTwoVariationLevels();
        $expectedProductModelIds[] = $this->givenAnotherImpactedRootProductModelWithTwoVariationLevels();
        $expectedProductModelIds[] = $this->givenASubProductModelImpactedByACommonAttribute();
        $expectedProductModelIds[] = $this->givenASubProductModelImpactedByALevelOneVariantAttribute();

        $this->givenARootProductModelNotImpactedBecauseOfItsVariantFamily();
        $this->givenARootProductModelNotImpactedBecauseOfItsLevelOneAttributes();

        $this->givenASubProductModelNotImpactedBecauseOfItsVariantFamily();
        $this->givenASubProductModelNotImpactedBecauseOfItsLevelTwoAttributes();

        $productModelIds = \array_merge(
            \iterator_to_array($this->get(GetProductModelIdsImpactedByAttributeGroupActivationQuery::class)->forAttributeGroup(new AttributeGroupCode('recently_activated'), 2)),
            \iterator_to_array($this->get(GetProductModelIdsImpactedByAttributeGroupActivationQuery::class)->forAttributeGroup(new AttributeGroupCode('recently_deactivated'), 2)),
        );
        $productModelIds = array_map(fn (ProductModelIdCollection $collection) => $collection->toArray(), $productModelIds);

        $this->assertEqualsCanonicalizing($expectedProductModelIds, array_merge_recursive(...$productModelIds));
    }

    private function givenAnImpactedRootProductModelWithASingleVariationLevel(): ProductModelId
    {
        $this->createFamily('impacted_family_A', ['attributes' => ['name', 'weight', 'color']]);
        $this->createFamilyVariant('impacted_family_variant_A', 'impacted_family_A', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
            ],
        ]);

        $productModel = $this->createProductModel('ImpactedRootProductModelWithASingleVariationLevel', 'impacted_family_variant_A');

        return new ProductModelId($productModel->getId());
    }

    private function givenAnImpactedRootProductModelWithTwoVariationLevels(): ProductModelId
    {
        $this->createFamily('impacted_family_A2', ['attributes' => ['name', 'weight', 'color', 'size']]);
        $this->createFamilyVariant('impacted_family_variant_A2', 'impacted_family_A2', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => [],
                ],
            ],
        ]);

        $productModel = $this->createProductModel('ImpactedRootProductModelWithTwoVariationLevels', 'impacted_family_variant_A2');

        return new ProductModelId($productModel->getId());
    }

    private function givenAnotherImpactedRootProductModelWithTwoVariationLevels(): ProductModelId
    {
        $productModel = $this->createProductModel('AnotherImpactedRootProductModelWithTwoVariationLevels', 'impacted_family_variant_A2');

        return new ProductModelId($productModel->getId());
    }

    private function givenARootProductModelNotImpactedBecauseOfItsVariantFamily(): void
    {
        $this->createFamily('not_impacted_family_A', ['attributes' => ['name', 'description', 'color']]);
        $this->createFamilyVariant('not_impacted_family_variant_A', 'not_impacted_family_A', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
            ],
        ]);

        $this->createProductModel('RootProductModelNotImpactedBecauseOfItsVariantFamily', 'not_impacted_family_variant_A');
    }

    private function givenARootProductModelNotImpactedBecauseOfItsLevelOneAttributes(): void
    {
        $this->createFamilyVariant('remove_impact_family_variant_A', 'impacted_family_A', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['weight'],
                ],
            ],
        ]);

        $this->createProductModel('RootProductModelNotImpactedBecauseOfItsLevelOneAttributes', 'remove_impact_family_variant_A');
    }

    private function givenASubProductModelImpactedByACommonAttribute(): ProductModelId
    {
        $subProductModel = $this->createSubProductModel('SubProductModelImpactedByACommonAttribute', 'impacted_family_variant_A2', 'ImpactedRootProductModelWithTwoVariationLevels');

        return new ProductModelId($subProductModel->getId());
    }

    private function givenASubProductModelImpactedByALevelOneVariantAttribute(): ProductModelId
    {
        $this->createFamily('impacted_family_B2', ['attributes' => ['name', 'weight', 'color', 'size']]);
        $this->createFamilyVariant('impacted_family_variant_B2', 'impacted_family_B2', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['weight'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => [],
                ],
            ],
        ]);

        $parent = $this->createProductModel('ParentSubProductModelImpactedByALevelOneVariantAttribute', 'impacted_family_variant_B2');
        $subProductModel = $this->createSubProductModel('SubProductModelImpactedByALevelOneVariantAttribute', 'impacted_family_variant_B2', $parent->getCode());

        return new ProductModelId($subProductModel->getId());
    }

    private function givenASubProductModelNotImpactedBecauseOfItsVariantFamily(): void
    {
        $this->createFamily('not_impacted_family_B', ['attributes' => ['name', 'description', 'color', 'size']]);
        $this->createFamilyVariant('not_impacted_family_variant_B2', 'not_impacted_family_B', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => [],
                ],
            ],
        ]);

        $parent = $this->createProductModel('ParentSubProductModelNotImpactedBecauseOfItsVariantFamily', 'not_impacted_family_variant_B2');
        $this->createSubProductModel('SubProductModelNotImpactedBecauseOfItsVariantFamily', 'not_impacted_family_variant_B2', $parent->getCode());
    }

    public function givenASubProductModelNotImpactedBecauseOfItsLevelTwoAttributes(): void
    {
        $this->createFamilyVariant('remove_impact_family_variant_A2', 'impacted_family_A2', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => ['weight'],
                ],
            ],
        ]);

        $parent = $this->createProductModel('ParentSubProductModelNotImpactedBecauseOfItsLevelTwoAttributes', 'remove_impact_family_variant_A2');
        $this->createSubProductModel('SubProductModelNotImpactedBecauseOfItsLevelTwoAttributes', 'remove_impact_family_variant_A2', $parent->getCode());
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

    private function createAttributeGroupWithAttributesForVariationAxes(string $code, array $attributes, bool $activated, \DateTimeImmutable $activationUpdatedAt): int
    {
        foreach ($attributes as $attributeCode) {
            $this->createAttribute($attributeCode, ['type' => AttributeTypes::OPTION_SIMPLE_SELECT,]);
            $this->createAttributeOptions($attributeCode, ['optionA', 'optionB', 'optionC']);
        }

        $attributeGroup = $this->createAttributeGroup($code, ['attributes' => $attributes]);

        $this->createAttributeGroupActivation($code, $activated, $activationUpdatedAt);

        return $attributeGroup->getId();
    }
}
