<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetPendingCriteriaEvaluationsByProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetPendingCriteriaEvaluationsByProductIdsQuery implements GetPendingCriteriaEvaluationsByProductIdsQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    /** @var Clock */
    private $clock;

    /** @var string */
    private $tableName;

    public function __construct(Connection $dbConnection, Clock $clock, string $tableName)
    {
        $this->dbConnection = $dbConnection;
        $this->clock = $clock;
        $this->tableName = $tableName;
    }

    /**
     * @inheritDoc
     */
    public function execute(ProductIdCollection $productIds): array
    {
        if ($productIds->isEmpty()) {
            return [];
        }

        $criterionEvaluationTable = $this->tableName;

        if ('pim_data_quality_insights_product_criteria_evaluation' === $criterionEvaluationTable) {
            $sql = <<<SQL
SELECT p.id as product_id, JSON_ARRAYAGG(criterion_code) as criteria
FROM $criterionEvaluationTable e
    JOIN pim_catalog_product p ON p.uuid = e.product_uuid
WHERE status = :status
AND p.id IN(:product_ids)
GROUP BY p.id
SQL;
        } else {
            $sql = <<<SQL
SELECT product_id, JSON_ARRAYAGG(criterion_code) as criteria
FROM $criterionEvaluationTable
WHERE status = :status
AND product_id IN(:product_ids)
GROUP BY product_id
SQL;
        }

        $params = [
            'status' => CriterionEvaluationStatus::PENDING,
            'product_ids' => $productIds->toArrayInt(),
        ];

        $types = [
            'status' => \PDO::PARAM_STR,
            'product_ids' => Connection::PARAM_INT_ARRAY,
        ];

        $stmt = $this->dbConnection->executeQuery($sql, $params, $types);

        $productsCriteriaEvaluations = [];
        while ($resultRow = $stmt->fetchAssociative()) {
            $productId = ProductId::fromString($resultRow['product_id']);
            $criteria = json_decode($resultRow['criteria']);
            $productsCriteriaEvaluations[$productId->toInt()] = $this->hydrateProductCriteriaEvaluations($productId, $criteria);
        }

        return $productsCriteriaEvaluations;
    }

    private function hydrateProductCriteriaEvaluations(ProductId $productId, array $criteria): Write\CriterionEvaluationCollection
    {
        $productCriteriaEvaluations = new Write\CriterionEvaluationCollection();
        $pendingStatus = CriterionEvaluationStatus::pending();

        foreach ($criteria as $criterion) {
            $productCriteriaEvaluations->add(new Write\CriterionEvaluation(
                new CriterionCode($criterion),
                $productId,
                $pendingStatus
            ));
        }

        return $productCriteriaEvaluations;
    }
}
