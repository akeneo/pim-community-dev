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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetPendingCriteriaEvaluationsByProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

final class GetPendingCriteriaEvaluationsByProductIdsQuery implements GetPendingCriteriaEvaluationsByProductIdsQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    /** @var Clock */
    private $clock;

    /** @var string */
    private $tableName;

    public function __construct(\Doctrine\DBAL\Driver\Connection $dbConnection, Clock $clock, string $tableName)
    {
        $this->dbConnection = $dbConnection;
        $this->clock = $clock;
        $this->tableName = $tableName;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $criterionEvaluationTable = $this->tableName;

        $sql = <<<SQL
SELECT product_id, JSON_ARRAYAGG(criterion_code) as criteria
FROM $criterionEvaluationTable
WHERE status = :status
AND product_id IN(:product_ids)
GROUP BY product_id
SQL;
        $params = [
            'status' => CriterionEvaluationStatus::PENDING,
            'product_ids' => $productIds,
        ];

        $types = [
            'status' => \PDO::PARAM_STR,
            'product_ids' => Connection::PARAM_INT_ARRAY,
        ];

        $stmt = $this->dbConnection->executeQuery($sql, $params, $types);

        $productsCriteriaEvaluations = [];
        while ($resultRow = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $productId = new ProductId((int) $resultRow['product_id']);
            $criteria = json_decode($resultRow['criteria']);
            $productsCriteriaEvaluations[$productId->toInt()] = $this->hydrateProductCriteriaEvaluations($productId, $criteria);
        }

        return $productsCriteriaEvaluations;
    }

    private function hydrateProductCriteriaEvaluations(ProductId $productId, array $criteria): CriterionEvaluationCollection
    {
        $productCriteriaEvaluations = new CriterionEvaluationCollection();
        $pendingStatus = CriterionEvaluationStatus::pending();

        foreach ($criteria as $criterion) {
            $productCriteriaEvaluations->add(new CriterionEvaluation(
                new CriterionCode($criterion),
                $productId,
                $pendingStatus
            ));
        }

        return $productCriteriaEvaluations;
    }
}
