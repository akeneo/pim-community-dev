<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

final class GetLatestCriteriaEvaluationsByProductIdQuery implements GetLatestCriteriaEvaluationsByProductIdQueryInterface
{
    /** @var Connection */
    private $db;

    /** @var Clock */
    private $clock;

    /** @var string */
    private $tableName;

    public function __construct(Connection $db, Clock $clock, string $tableName)
    {
        $this->db = $db;
        $this->clock = $clock;
        $this->tableName = $tableName;
    }

    public function execute(ProductId $productId): Read\CriterionEvaluationCollection
    {
        $criterionEvaluationTable = $this->tableName;

        $sql = <<<SQL
SELECT 
       latest_evaluation.id,
       latest_evaluation.product_id,
       latest_evaluation.criterion_code,
       latest_evaluation.status,
       latest_evaluation.created_at,
       latest_evaluation.started_at,
       latest_evaluation.ended_at,
       latest_evaluation.result
FROM $criterionEvaluationTable AS latest_evaluation
LEFT JOIN $criterionEvaluationTable AS other_evaluation
    ON other_evaluation.product_id = :product_id
    AND latest_evaluation.criterion_code = other_evaluation.criterion_code
    AND latest_evaluation.created_at < other_evaluation.created_at
WHERE latest_evaluation.product_id = :product_id
    AND other_evaluation.id IS NULL;
SQL;

        $rows = $this->db->executeQuery(
            $sql,
            ['product_id' => $productId->toInt()],
            ['product_id' => \PDO::PARAM_INT]
        )->fetchAll(FetchMode::ASSOCIATIVE);

        $criteriaEvaluations = new Read\CriterionEvaluationCollection();
        foreach ($rows as $rawCriterionEvaluation) {
            $criteriaEvaluations->add(new Read\CriterionEvaluation(
                new CriterionEvaluationId($rawCriterionEvaluation['id']),
                new CriterionCode($rawCriterionEvaluation['criterion_code']),
                new ProductId(intval($rawCriterionEvaluation['product_id'])),
                $this->clock->fromString($rawCriterionEvaluation['created_at']),
                new CriterionEvaluationStatus($rawCriterionEvaluation['status']),
                $this->hydrateCriterionEvaluationResult($rawCriterionEvaluation['result']),
                null !== $rawCriterionEvaluation['started_at'] ? $this->clock->fromString($rawCriterionEvaluation['started_at']) : null,
                null !== $rawCriterionEvaluation['ended_at'] ? $this->clock->fromString($rawCriterionEvaluation['ended_at']) : null
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
        $rates = ChannelLocaleRateCollection::fromArrayInt($rawResult['rates'] ?? []);
        $status = CriterionEvaluationResultStatusCollection::fromArrayString($rawResult['status'] ?? []);

        return new Read\CriterionEvaluationResult($rates, $status, $rawResult['data'] ?? []);
    }
}
