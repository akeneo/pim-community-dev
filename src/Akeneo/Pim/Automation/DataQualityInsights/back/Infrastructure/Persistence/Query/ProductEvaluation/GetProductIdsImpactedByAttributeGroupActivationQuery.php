<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdsImpactedByAttributeGroupActivationQuery implements GetEntityIdsImpactedByAttributeGroupActivationQueryInterface
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
        $impactedFamilies = $this->retrieveFamiliesWithAttributeGroupActivationUpdatedSince($updatedSince);

        if (empty($impactedFamilies)) {
            return;
        }

        $query = <<<SQL
SELECT BIN_TO_UUID(product.uuid) FROM pim_catalog_product AS product WHERE product.family_id IN (:families)
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['families' => $impactedFamilies],
            ['families' => Connection::PARAM_INT_ARRAY,]
        );

        $productUuids = [];
        while ($productUuid = $stmt->fetchOne()) {
            $productUuids[] = $productUuid;

            if (count($productUuids) >= $bulkSize) {
                yield $this->idFactory->createCollection($productUuids);
                $productUuids = [];
            }
        }

        if (!empty($productUuids)) {
            yield $this->idFactory->createCollection($productUuids);
        }
    }

    private function retrieveFamiliesWithAttributeGroupActivationUpdatedSince(\DateTimeImmutable $updatedSince): array
    {
        $query = <<<SQL
SELECT DISTINCT family_attribute.family_id
FROM pim_data_quality_insights_attribute_group_activation AS attribute_group_activation
    INNER JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.code = attribute_group_activation.attribute_group_code
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.group_id = attribute_group.id
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
WHERE attribute_group_activation.updated_at > :updatedSince
SQL;

        $stmt = $this->dbConnection->executeQuery($query, ['updatedSince' => $updatedSince->format(Clock::TIME_FORMAT)]);

        return $stmt->fetchFirstColumn();
    }

    /**
     * @return \Generator<int, ProductEntityIdCollection>
     */
    public function forAttributeGroup(AttributeGroupCode $attributeGroupCode, int $bulkSize): \Generator
    {
        $impactedFamilies = $this->retrieveFamilyCodesWithAttributeGroup($attributeGroupCode);
        if ([] === $impactedFamilies) {
            return;
        }

        $stmt = $this->dbConnection->executeQuery(
            'SELECT BIN_TO_UUID(product.uuid) FROM pim_catalog_product AS product WHERE product.family_id IN (:families)',
            ['families' => $impactedFamilies],
            ['families' => Connection::PARAM_INT_ARRAY,]
        );

        $productUuids = [];
        while ($productUuid = $stmt->fetchOne()) {
            $productUuids[] = $productUuid;

            if (count($productUuids) >= $bulkSize) {
                yield $this->idFactory->createCollection($productUuids);
                $productUuids = [];
            }
        }

        if (count($productUuids) > 0) {
            yield $this->idFactory->createCollection($productUuids);
        }
    }

    /**
     * @return string[]
     */
    private function retrieveFamilyCodesWithAttributeGroup(AttributeGroupCode $attributeGroupCode): array
    {
        $query = <<<SQL
SELECT DISTINCT family_attribute.family_id
FROM pim_catalog_attribute_group AS attribute_group
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.group_id = attribute_group.id
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
WHERE attribute_group.code = :attribute_group_code
SQL;

        $stmt = $this->dbConnection->executeQuery($query, ['attribute_group_code' => (string) $attributeGroupCode]);

        return $stmt->fetchFirstColumn();
    }
}
