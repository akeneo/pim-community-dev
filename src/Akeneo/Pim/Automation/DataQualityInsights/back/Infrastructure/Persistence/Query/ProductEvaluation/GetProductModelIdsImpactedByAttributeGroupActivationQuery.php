<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelIdsImpactedByAttributeGroupActivationQuery implements GetEntityIdsImpactedByAttributeGroupActivationQueryInterface
{
    public function __construct(
        private Connection                      $dbConnection,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    /**
     * @return \Generator<int, ProductEntityIdCollection>
     */
    public function updatedSince(\DateTimeImmutable $updatedSince, int $bulkSize): \Generator
    {
        $productModelIds = [];
        $stmtRootProductModels = $this->executeQueryToRetrieveImpactedRootProductModels($updatedSince);

        while ($productModelId = $stmtRootProductModels->fetchOne()) {
            $productModelIds[] = $productModelId;
            if (count($productModelIds) >= $bulkSize) {
                yield $this->idFactory->createCollection($productModelIds);
                $productModelIds = [];
            }
        }

        $stmtSubProductModels = $this->executeQueryToRetrieveImpactedSubProductModels($updatedSince);

        while ($productModelId = $stmtSubProductModels->fetchOne()) {
            $productModelIds[] = $productModelId;
            if (count($productModelIds) >= $bulkSize) {
                yield $this->idFactory->createCollection($productModelIds);
                $productModelIds = [];
            }
        }


        if (!empty($productModelIds)) {
            yield $this->idFactory->createCollection($productModelIds);
        }
    }

    private function executeQueryToRetrieveImpactedRootProductModels(\DateTimeImmutable $updatedSince): Result
    {
        $familyVariantIds = $this->retrieveFamilyVariantsWithAttributeGroupActivationUpdatedSince($updatedSince);

        $query = <<<SQL
SELECT DISTINCT product_model.id
FROM pim_catalog_product_model AS product_model
WHERE product_model.family_variant_id IN (:familyVariants)
    AND product_model.parent_id IS NULL
SQL;

        return $this->dbConnection->executeQuery(
            $query,
            ['familyVariants' => $familyVariantIds],
            ['familyVariants' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function retrieveFamilyVariantsWithAttributeGroupActivationUpdatedSince(\DateTimeImmutable $updatedSince): array
    {
        $query = <<<SQL
SELECT DISTINCT family_variant.id
FROM pim_data_quality_insights_attribute_group_activation AS activation
    INNER JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.code = activation.attribute_group_code
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.group_id = attribute_group.id
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
    INNER JOIN pim_catalog_family AS family ON family.id = family_attribute.family_id
    INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.family_id = family.id
WHERE activation.updated_at > :updatedSince
    AND NOT EXISTS(
        SELECT 1 FROM pim_catalog_variant_attribute_set_has_attributes AS attribute_set_attributes
            INNER JOIN pim_catalog_family_variant_attribute_set AS attribute_set ON attribute_set.id = attribute_set_attributes.variant_attribute_set_id
            INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets AS family_attribute_set ON family_attribute_set.variant_attribute_sets_id = attribute_set.id
        WHERE attribute_set_attributes.attributes_id = attribute.id
            AND family_attribute_set.family_variant_id = family_variant.id
    );
SQL;

        $stmt = $this->dbConnection->executeQuery($query, ['updatedSince' => $updatedSince->format(Clock::TIME_FORMAT)]);

        return $stmt->fetchFirstColumn();
    }

    private function executeQueryToRetrieveImpactedSubProductModels(\DateTimeImmutable $updatedSince): Result
    {
        $familyVariantIds = $this->retrieveLevelTwoFamilyVariantsWithAttributeGroupActivationUpdatedSince($updatedSince);

        $query = <<<SQL
SELECT DISTINCT product_model.id
FROM pim_catalog_product_model AS product_model
WHERE product_model.family_variant_id IN (:familyVariants)
    AND product_model.parent_id IS NOT NULL
SQL;

        return $this->dbConnection->executeQuery(
            $query,
            ['familyVariants' => $familyVariantIds],
            ['familyVariants' => Connection::PARAM_INT_ARRAY,]
        );
    }

    private function retrieveLevelTwoFamilyVariantsWithAttributeGroupActivationUpdatedSince(\DateTimeImmutable $updatedSince): array
    {
        $query = <<<SQL
SELECT DISTINCT family_variant.id
FROM pim_data_quality_insights_attribute_group_activation AS activation
    INNER JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.code = activation.attribute_group_code
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.group_id = attribute_group.id
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
    INNER JOIN pim_catalog_family AS family ON family.id = family_attribute.family_id
    INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.family_id = family.id
    INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets AS family_attribute_set ON family_attribute_set.family_variant_id = family_variant.id
    INNER JOIN pim_catalog_family_variant_attribute_set AS attribute_set_level_2
        ON attribute_set_level_2.id = family_attribute_set.variant_attribute_sets_id
        AND attribute_set_level_2.level = 2
WHERE activation.updated_at > :updatedSince
    AND NOT EXISTS(
        SELECT 1 FROM pim_catalog_variant_attribute_set_has_attributes AS attribute_set_attributes_level_2
        WHERE attribute_set_attributes_level_2.variant_attribute_set_id = attribute_set_level_2.id
        AND attribute_set_attributes_level_2.attributes_id = attribute.id
    );
SQL;

        $stmt = $this->dbConnection->executeQuery($query, ['updatedSince' => $updatedSince->format(Clock::TIME_FORMAT)]);

        return $stmt->fetchFirstColumn();
    }

    /**
     * @return \Generator<int, ProductEntityIdCollection>
     */
    public function forAttributeGroup(AttributeGroupCode $attributeGroupCode, int $bulkSize): \Generator
    {
        $productModelIds = [];
        $stmtRootProductModels = $this->executeQueryToRetrieveImpactedRootProductModelsForAttributeGroup($attributeGroupCode);
        while ($productModelId = $stmtRootProductModels->fetchOne()) {
            $productModelIds[] = $productModelId;
            if (count($productModelIds) >= $bulkSize) {
                yield $this->idFactory->createCollection($productModelIds);
                $productModelIds = [];
            }
        }

        $stmtSubProductModels = $this->executeQueryToRetrieveImpactedSubProductModelsForAttributeGroup($attributeGroupCode);
        while ($productModelId = $stmtSubProductModels->fetchOne()) {
            $productModelIds[] = $productModelId;
            if (count($productModelIds) >= $bulkSize) {
                yield $this->idFactory->createCollection($productModelIds);
                $productModelIds = [];
            }
        }


        if (count($productModelIds) > 0) {
            yield $this->idFactory->createCollection($productModelIds);
        }
    }

    private function executeQueryToRetrieveImpactedRootProductModelsForAttributeGroup(
        AttributeGroupCode $attributeGroupCode
    ): Result {
        $query = <<<SQL
SELECT DISTINCT family_variant.id
FROM pim_catalog_attribute_group AS attribute_group
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.group_id = attribute_group.id
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
    INNER JOIN pim_catalog_family AS family ON family.id = family_attribute.family_id
    INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.family_id = family.id
WHERE attribute_group.code = :attribute_group_code
    AND NOT EXISTS(
        SELECT 1 FROM pim_catalog_variant_attribute_set_has_attributes AS attribute_set_attributes
            INNER JOIN pim_catalog_family_variant_attribute_set AS attribute_set ON attribute_set.id = attribute_set_attributes.variant_attribute_set_id
            INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets AS family_attribute_set ON family_attribute_set.variant_attribute_sets_id = attribute_set.id
        WHERE attribute_set_attributes.attributes_id = attribute.id
            AND family_attribute_set.family_variant_id = family_variant.id
    );
SQL;

        $familyVariantIds = $this->dbConnection
            ->executeQuery($query, ['attribute_group_code' => (string) $attributeGroupCode])
            ->fetchFirstColumn();

        $query = <<<SQL
SELECT DISTINCT product_model.id
FROM pim_catalog_product_model AS product_model
WHERE product_model.family_variant_id IN (:familyVariants)
    AND product_model.parent_id IS NULL
SQL;

        return $this->dbConnection->executeQuery(
            $query,
            ['familyVariants' => $familyVariantIds],
            ['familyVariants' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function executeQueryToRetrieveImpactedSubProductModelsForAttributeGroup(
        AttributeGroupCode $attributeGroupCode
    ): Result {
        $query = <<<SQL
SELECT DISTINCT family_variant.id
FROM pim_catalog_attribute_group AS attribute_group
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.group_id = attribute_group.id
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
    INNER JOIN pim_catalog_family AS family ON family.id = family_attribute.family_id
    INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.family_id = family.id
    INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets AS family_attribute_set ON family_attribute_set.family_variant_id = family_variant.id
    INNER JOIN pim_catalog_family_variant_attribute_set AS attribute_set_level_2
        ON attribute_set_level_2.id = family_attribute_set.variant_attribute_sets_id
        AND attribute_set_level_2.level = 2
WHERE attribute_group.code = :attribute_group_code
    AND NOT EXISTS(
        SELECT 1 FROM pim_catalog_variant_attribute_set_has_attributes AS attribute_set_attributes_level_2
        WHERE attribute_set_attributes_level_2.variant_attribute_set_id = attribute_set_level_2.id
        AND attribute_set_attributes_level_2.attributes_id = attribute.id
    );
SQL;

        $familyVariantIds = $this->dbConnection
            ->executeQuery($query, ['attribute_group_code' => (string) $attributeGroupCode])
            ->fetchFirstColumn();

        $query = <<<SQL
SELECT DISTINCT product_model.id
FROM pim_catalog_product_model AS product_model
WHERE product_model.family_variant_id IN (:familyVariants)
    AND product_model.parent_id IS NOT NULL
SQL;

        return $this->dbConnection->executeQuery(
            $query,
            ['familyVariants' => $familyVariantIds],
            ['familyVariants' => Connection::PARAM_INT_ARRAY]
        );
    }
}
