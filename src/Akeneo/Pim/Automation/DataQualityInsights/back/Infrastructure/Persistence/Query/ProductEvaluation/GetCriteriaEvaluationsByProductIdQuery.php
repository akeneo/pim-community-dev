<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCriteriaEvaluationsByProductIdQuery implements GetCriteriaEvaluationsByProductIdQueryInterface
{
    private Connection $db;

    private Clock $clock;

    private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds;

    private string $tableName;

    public function __construct(
        Connection $db,
        Clock $clock,
        TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds,
        string $tableName
    ) {

        $this->db = $db;
        $this->clock = $clock;
        $this->transformCriterionEvaluationResultIds = $transformCriterionEvaluationResultIds;
        $this->tableName = $tableName;
    }

    public function execute(ProductId $productId): Read\CriterionEvaluationCollection
    {
        $criterionEvaluationTable = $this->tableName;

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

        $rows = $this->db->executeQuery(
            $sql,
            ['product_id' => $productId->toInt()],
            ['product_id' => \PDO::PARAM_INT]
        )->fetchAll(FetchMode::ASSOCIATIVE);

        $criteriaEvaluations = new Read\CriterionEvaluationCollection();
        foreach ($rows as $rawCriterionEvaluation) {
            $criteriaEvaluations->add(new Read\CriterionEvaluation(
                new CriterionCode($rawCriterionEvaluation['criterion_code']),
                new ProductId(intval($rawCriterionEvaluation['product_id'])),
                null !== $rawCriterionEvaluation['evaluated_at'] ? $this->clock->fromString($rawCriterionEvaluation['evaluated_at']) : null,
                new CriterionEvaluationStatus($rawCriterionEvaluation['status']),
                $this->hydrateCriterionEvaluationResult($rawCriterionEvaluation['result']),
            ));
        }

        return $criteriaEvaluations;
    }

    private function hydrateCriterionEvaluationResult($rawResult): ?Read\CriterionEvaluationResult
    {
        if (null === $rawResult) {
            return null;
        }

        $rawResult = json_decode($rawResult, true, JSON_THROW_ON_ERROR);
        $rawResult = $this->transformCriterionEvaluationResultIds->transformToCodes($rawResult);

        $rates = ChannelLocaleRateCollection::fromArrayInt($rawResult['rates'] ?? []);
        $status = CriterionEvaluationResultStatusCollection::fromArrayString($rawResult['status'] ?? []);

        return new Read\CriterionEvaluationResult($rates, $status, $rawResult['data'] ?? []);
    }
}
