<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// TODO This class manage Products and ProductModels. Should be splitted into 2 classes
final class GetCriteriaEvaluationsByProductIdQuery implements GetCriteriaEvaluationsByProductIdQueryInterface
{
    private Connection $db;

    private Clock $clock;

    private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds;

    private string $tableName;

    public function __construct(
        Connection                            $db,
        Clock                                 $clock,
        TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds,
        string                                $tableName
    ) {
        $this->db = $db;
        $this->clock = $clock;
        $this->transformCriterionEvaluationResultIds = $transformCriterionEvaluationResultIds;
        $this->tableName = $tableName;
    }

    public function execute(ProductEntityIdInterface $productId): Read\CriterionEvaluationCollection
    {
        $criterionEvaluationTable = $this->tableName;

        if ('pim_data_quality_insights_product_criteria_evaluation' === $criterionEvaluationTable) {
            $sql = <<<SQL
SELECT
       product.id AS product_id,
       evaluation.criterion_code,
       evaluation.status,
       evaluation.evaluated_at,
       evaluation.result
FROM $criterionEvaluationTable AS evaluation
    JOIN pim_catalog_product product ON product.uuid = evaluation.product_uuid
WHERE product.id = :product_id
SQL;
        } else {
            $sql = <<<SQL
SELECT
       evaluation.product_id,
       evaluation.criterion_code,
       evaluation.status,
       evaluation.evaluated_at,
       evaluation.result
FROM $criterionEvaluationTable AS evaluation
WHERE evaluation.product_id = :product_id
SQL;
        }

        $rows = $this->db->executeQuery(
            $sql,
            ['product_id' => (int)(string)$productId],
            ['product_id' => \PDO::PARAM_INT]
        )->fetchAllAssociative();

        $criteriaEvaluations = new Read\CriterionEvaluationCollection();
        foreach ($rows as $rawCriterionEvaluation) {
            $criterionCode = new CriterionCode($rawCriterionEvaluation['criterion_code']);
            $criteriaEvaluations->add(new Read\CriterionEvaluation(
                $criterionCode,
                $productId,
                null !== $rawCriterionEvaluation['evaluated_at'] ? $this->clock->fromString($rawCriterionEvaluation['evaluated_at']) : null,
                new CriterionEvaluationStatus($rawCriterionEvaluation['status']),
                $this->hydrateCriterionEvaluationResult($criterionCode, $rawCriterionEvaluation['result']),
            ));
        }

        return $criteriaEvaluations;
    }

    private function hydrateCriterionEvaluationResult(CriterionCode $criterionCode, $rawResult): ?Read\CriterionEvaluationResult
    {
        if (null === $rawResult) {
            return null;
        }

        $rawResult = json_decode($rawResult, true, JSON_THROW_ON_ERROR);
        $rawResult = $this->transformCriterionEvaluationResultIds->transformToCodes($criterionCode, $rawResult);

        return Read\CriterionEvaluationResult::fromArray($rawResult);
    }
}
