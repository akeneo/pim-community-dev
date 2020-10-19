<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelIdsImpactedByAttributeGroupActivationQuery implements GetProductIdsImpactedByAttributeGroupActivationQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function updatedSince(\DateTimeImmutable $updatedSince, int $bulkSize): \Iterator
    {
        $productModelIds = [];
        $stmtRootProductModels = $this->executeQueryToRetrieveImpactedRootProductModels($updatedSince);

        while ($productModelId = $stmtRootProductModels->fetchColumn()) {
            $productModelIds[] = new ProductId(intval($productModelId));
            if (count($productModelIds) >= $bulkSize) {
                yield $productModelIds;
                $productModelIds = [];
            }
        }

        $stmtSubProductModels = $this->executeQueryToRetrieveImpactedSubProductModels($updatedSince);

        while ($productModelId = $stmtSubProductModels->fetchColumn()) {
            $productModelIds[] = new ProductId(intval($productModelId));
            if (count($productModelIds) >= $bulkSize) {
                yield $productModelIds;
                $productModelIds = [];
            }
        }


        if (!empty($productModelIds)) {
            yield $productModelIds;
        }
    }

    private function executeQueryToRetrieveImpactedRootProductModels(\DateTimeImmutable $updatedSince): ResultStatement
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

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function executeQueryToRetrieveImpactedSubProductModels(\DateTimeImmutable $updatedSince): ResultStatement
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

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
