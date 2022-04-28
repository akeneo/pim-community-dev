<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetPendingCriteriaEvaluationsByEntityIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ForwardCompatibility\DriverResultStatement;
use Doctrine\DBAL\ForwardCompatibility\Result;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetPendingCriteriaEvaluationsByEntityIdsQuery implements GetPendingCriteriaEvaluationsByEntityIdsQueryInterface
{
    public function __construct(
        private Connection                      $dbConnection,
        private string                          $tableName,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(ProductEntityIdCollection $entityIds): array
    {
        if ($entityIds->isEmpty()) {
            return [];
        }

        $entityCriteriaEvaluationStatement = [];
        if ($entityIds instanceof ProductUuidCollection) {
            $entityCriteriaEvaluationStatement = $this->executeForProductUuidCollection($entityIds);
        } elseif ($entityIds instanceof ProductModelIdCollection) {
            $entityCriteriaEvaluationStatement = $this->executeForProductModelIdCollection($entityIds);
        }

        $productsCriteriaEvaluations = [];
        while ($resultRow = $entityCriteriaEvaluationStatement->fetchAssociative()) {
            $entityId = $this->idFactory->create($resultRow['entity_id']);
            $criteria = json_decode($resultRow['criteria']);
            $productsCriteriaEvaluations[(string)$entityId] = $this->hydrateProductCriteriaEvaluations($entityId, $criteria);
        }

        return $productsCriteriaEvaluations;
    }

    private function executeForProductUuidCollection(ProductUuidCollection $productUuids): Result|DriverResultStatement
    {
        $criterionEvaluationTable = $this->tableName;

        $sql = <<<SQL
SELECT BIN_TO_UUID(p.uuid) as entity_id, JSON_ARRAYAGG(criterion_code) as criteria
FROM $criterionEvaluationTable e
    JOIN pim_catalog_product p ON p.uuid = e.product_uuid
WHERE status = :status
AND p.uuid IN(:product_uuids)
GROUP BY p.uuid
SQL;

        return $this->dbConnection->executeQuery($sql, [
            'status' => CriterionEvaluationStatus::PENDING,
            'product_uuids' => $productUuids->toArrayBytes(),
        ], [
            'status' => \PDO::PARAM_STR,
            'product_uuids' => Connection::PARAM_STR_ARRAY,
        ]);
    }

    private function executeForProductModelIdCollection(ProductModelIdCollection $productModelIdCollection): Result|DriverResultStatement
    {
        $criterionEvaluationTable = $this->tableName;

        $sql = <<<SQL
SELECT product_id AS entity_id, JSON_ARRAYAGG(criterion_code) as criteria
FROM $criterionEvaluationTable
WHERE status = :status
AND product_id IN(:product_model_ids)
GROUP BY product_id
SQL;

        return $this->dbConnection->executeQuery($sql, [
            'status' => CriterionEvaluationStatus::PENDING,
            'product_model_ids' => $productModelIdCollection->toArrayString(),
        ], [
            'status' => \PDO::PARAM_STR,
            'product_model_ids' => Connection::PARAM_INT_ARRAY,
        ]);
    }

    private function hydrateProductCriteriaEvaluations(ProductEntityIdInterface $entityId, array $criteria): Write\CriterionEvaluationCollection
    {
        $productCriteriaEvaluations = new Write\CriterionEvaluationCollection();
        $pendingStatus = CriterionEvaluationStatus::pending();

        foreach ($criteria as $criterion) {
            $productCriteriaEvaluations->add(new Write\CriterionEvaluation(
                new CriterionCode($criterion),
                $entityId,
                $pendingStatus
            ));
        }

        return $productCriteriaEvaluations;
    }
}
