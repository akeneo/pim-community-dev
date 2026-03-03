<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelIdsToEvaluateQuery implements GetEntityIdsToEvaluateQueryInterface
{
    public function __construct(
        private Connection $db,
        private ProductModelIdFactory $idFactory
    ) {
    }

    /**
     * @return \Generator<int, ProductModelIdCollection>
     */
    public function execute(?int $limit = null, int $bulkSize = GetEntityIdsToEvaluateQueryInterface::BULK_SIZE): \Generator
    {
        $limitSql = null === $limit ? '' : sprintf('LIMIT %d', $limit);

        $sql = <<<SQL
SELECT DISTINCT product_id
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE status = :status
$limitSql
SQL;

        $stmt = $this->db->executeQuery($sql, ['status' => CriterionEvaluationStatus::PENDING], ['status' => \PDO::PARAM_STR]);

        $productModelIds = [];
        while ($productModelId = $stmt->fetchOne()) {
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
}
