<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdsToEvaluateQuery implements GetProductIdsToEvaluateQueryInterface
{
    public function __construct(private Connection $db, private ProductEntityIdFactoryInterface $idFactory)
    {
    }

    /**
     * @return \Generator<int, ProductIdCollection>
     */
    public function execute(int $limit, int $bulkSize): \Generator
    {
        $sql = <<<SQL
SELECT DISTINCT product_id
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE status = :status
LIMIT $limit
SQL;

        $stmt = $this->db->executeQuery($sql, ['status' => CriterionEvaluationStatus::PENDING], ['status' => \PDO::PARAM_STR]);

        $productIds = [];
        while ($productId = $stmt->fetchOne()) {
            $productIds[] = $productId;

            if (count($productIds) >= $bulkSize) {
                yield $this->idFactory->createCollection($productIds);
                $productIds = [];
            }
        }

        if (!empty($productIds)) {
            yield $this->idFactory->createCollection($productIds);
        }
    }
}
