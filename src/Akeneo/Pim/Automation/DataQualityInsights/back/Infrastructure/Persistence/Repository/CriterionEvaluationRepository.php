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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

final class CriterionEvaluationRepository implements CriterionEvaluationRepositoryInterface
{
    /** @var Connection */
    private $db;

    /** @var Clock */
    private $clock;

    public function __construct(Connection $db, Clock $clock)
    {
        $this->db = $db;
        $this->clock = $clock;
    }

    public function create(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        if (0 === $criteriaEvaluations->count()) {
            return;
        }

        $valuesPlaceholders = implode(',', array_fill(0, $criteriaEvaluations->count(), '(?, ?, ?, ?, ?, ?)'));

        $sql = <<<SQL
INSERT IGNORE INTO pimee_data_quality_insights_criteria_evaluation
    (id, criterion_code, product_id, created_at, status, pending)
VALUES
    $valuesPlaceholders
SQL;

        $statement = $this->db->prepare($sql);

        $valuePlaceholderIndex = 1;
        foreach ($criteriaEvaluations as $criterionEvaluation) {
            $statement->bindValue($valuePlaceholderIndex++, strval($criterionEvaluation->getId()));
            $statement->bindValue($valuePlaceholderIndex++, strval($criterionEvaluation->getCriterionCode()));
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getProductId()->toInt(), \PDO::PARAM_INT);
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getCreatedAt()->format(Clock::TIME_FORMAT));
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getStatus());
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->isPending() ? 1 : null, \PDO::PARAM_INT);
        }

        $statement->execute();
    }

    public function update(Write\CriterionEvaluation $criterionEvaluation): void
    {
        $sql = <<<'SQL'
UPDATE pimee_data_quality_insights_criteria_evaluation
SET
    criterion_code = :criterion_code,
    product_id = :product_id,
    created_at = :created_at,
    started_at = :started_at,
    ended_at = :ended_at,
    status = :status,
    pending = :pending,
    result = :result
WHERE id = :id
SQL;
        $result = null;
        $criterionEvaluationResult = $criterionEvaluation->getResult();
        if ($criterionEvaluationResult instanceof CriterionEvaluationResult) {
            $result = json_encode([
                'rates' => $criterionEvaluationResult->getRates()->toArrayInt(),
                'data' => $criterionEvaluationResult->getData(),
            ]);
        }

        $params = [
            'id' => strval($criterionEvaluation->getId()),
            'criterion_code' => strval($criterionEvaluation->getCriterionCode()),
            'product_id' => $criterionEvaluation->getProductId()->toInt(),
            'created_at' => $criterionEvaluation->getCreatedAt()->format(Clock::TIME_FORMAT),
            'started_at' => $criterionEvaluation->getStartedAt() instanceof \DateTimeImmutable ? $criterionEvaluation->getStartedAt()->format(Clock::TIME_FORMAT) : null,
            'ended_at' => $criterionEvaluation->getEndedAt() instanceof \DateTimeImmutable ? $criterionEvaluation->getEndedAt()->format(Clock::TIME_FORMAT) : null,
            'status' => strval($criterionEvaluation->getStatus()),
            'pending' => $criterionEvaluation->isPending() ? 1 : null,
            'result' => $result,
        ];

        $this->db->executeQuery($sql, $params);
    }

    public function findPendingByProductIds(array $productIds): ?array
    {
        if (empty($productIds)) {
            return [];
        }

        $sql = <<<'SQL'
SELECT * 
FROM pimee_data_quality_insights_criteria_evaluation 
WHERE status = :status 
AND product_id IN(:product_ids)
SQL;
        $params = [
            'status' => CriterionEvaluationStatus::PENDING,
            'product_ids' => $productIds,
        ];

        $types = [
            'status' => \PDO::PARAM_STR,
            'product_ids' => Connection::PARAM_INT_ARRAY,
        ];

        $stmt = $this->db->executeQuery($sql, $params, $types);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function (array $result) {
            return new Write\CriterionEvaluation(
                new CriterionEvaluationId($result['id']),
                new CriterionCode($result['criterion_code']),
                new ProductId(intval($result['product_id'])),
                $this->clock->fromString($result['created_at']),
                new CriterionEvaluationStatus($result['status'])
            );
        }, $results);
    }
}
