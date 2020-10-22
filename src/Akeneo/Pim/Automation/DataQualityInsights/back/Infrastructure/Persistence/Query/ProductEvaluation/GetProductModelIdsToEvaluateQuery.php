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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Doctrine\DBAL\Connection;

final class GetProductModelIdsToEvaluateQuery implements GetProductIdsToEvaluateQueryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function execute(int $limit, int $bulkSize): \Iterator
    {
        $sql = <<<SQL
SELECT DISTINCT product_id
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE status = :status
LIMIT $limit
SQL;

        $stmt = $this->db->executeQuery($sql, ['status' => CriterionEvaluationStatus::PENDING], ['status' => \PDO::PARAM_STR]);

        $productIds = [];
        while ($productId = $stmt->fetchColumn()) {
            $productIds[] = intval($productId);

            if (count($productIds) >= $bulkSize) {
                yield $productIds;
                $productIds = [];
            }
        }

        if (!empty($productIds)) {
            yield $productIds;
        }
    }
}
