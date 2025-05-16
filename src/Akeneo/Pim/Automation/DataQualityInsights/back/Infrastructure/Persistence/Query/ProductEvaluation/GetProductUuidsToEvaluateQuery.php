<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsToEvaluateQuery implements GetEntityIdsToEvaluateQueryInterface
{
    public function __construct(
        private Connection $db,
        private ProductUuidFactory $idFactory
    ) {
    }

    /**
     * @return \Generator<int, ProductUuidCollection>
     */
    public function execute(?int $limit = null, int $bulkSize = GetEntityIdsToEvaluateQueryInterface::BULK_SIZE): \Generator
    {
        $limitSql = null === $limit ? '' : sprintf('LIMIT %d', $limit);

        $sql = <<<SQL
SELECT DISTINCT BIN_TO_UUID(p.uuid) AS uuid
FROM pim_data_quality_insights_product_criteria_evaluation e
    JOIN pim_catalog_product p ON p.uuid = e.product_uuid
WHERE e.status = :status
$limitSql
SQL;

        $stmt = $this->db->executeQuery($sql, ['status' => CriterionEvaluationStatus::PENDING], ['status' => \PDO::PARAM_STR]);

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
}
