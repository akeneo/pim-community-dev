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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DeadlockException;

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

        $success = false;
        $retry = 0;

        while (!$success) {
            try {
                $statement->execute();
                $success = true;
            } catch (DeadlockException $e) {
                $retry++;
                if ($retry == 5) {
                    $this->executeWithLock($statement);
                    $success = true;
                } else {
                    usleep(rand(100000, 500000 * 2**$retry));
                }
            }
        }
    }

    /**
     * When reaching a certain number of retries we need to ensure the
     * transaction will succeed. This is done in locking the table and
     * serializing other transactions meanwhile. This method conflicts with
     * standard transaction and may also lock accesses to tables tied by
     * foreign keys. In order to ovoid stacking waiting transaction, we disable
     * foreign key checks during this process.
     */
    private function executeWithLock(\PDOStatement $statement): void
    {
        $value = $this->db->executeQuery('SELECT @@autocommit')->fetch();
        if (!isset($value['@@autocommit'])) {
            throw new \LogicException('Error when getting autocommit parameter from Mysql.');
        }

        $formerAutocommitValue = (int) $value['@@autocommit'];
        try {
            $this->db->executeQuery('SET autocommit=0');
            $this->db->executeQuery('LOCK TABLES pimee_data_quality_insights_criteria_evaluation WRITE');
            $statement->execute();
            $this->db->executeQuery('COMMIT');
        } finally {
            $this->db->executeQuery('UNLOCK TABLES');
            $this->db->executeQuery(sprintf('SET autocommit=%d', $formerAutocommitValue));
        }
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

        /**
         * @fixme Change the format to not duplicate every channel and locale for each attribute
         */
        if ($criterionEvaluationResult instanceof Write\CriterionEvaluationResult) {
            $result = json_encode([
                'rates' => $criterionEvaluationResult->getRates()->toArrayInt(),
                'status' => $criterionEvaluationResult->getStatus()->toArrayString(),
                'data' => $criterionEvaluationResult->getDataToArray(),
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

    public function purgeOutdatedEvaluations(int $batchSize, int $max): void
    {
        $query = <<<SQL
SELECT old_evaluations.id
FROM pimee_data_quality_insights_criteria_evaluation AS old_evaluations
INNER JOIN pimee_data_quality_insights_criteria_evaluation AS younger_evaluations
    ON younger_evaluations.product_id = old_evaluations.product_id
    AND younger_evaluations.criterion_code = old_evaluations.criterion_code
    AND younger_evaluations.created_at > old_evaluations.created_at
WHERE old_evaluations.id > :lastPurgedId
ORDER BY old_evaluations.id
LIMIT $batchSize
SQL;

        $purgedEvaluations = 0;
        $lastPurgedId = '';

        do {
            $evaluationsToPurge = $this->db
                ->executeQuery($query, ['lastPurgedId' => $lastPurgedId])
                ->fetchAll(\PDO::FETCH_COLUMN);

            $this->deleteByIds($evaluationsToPurge);
            $lastPurgedId = end($evaluationsToPurge);
            $purgedEvaluations += count($evaluationsToPurge);
        } while (!empty($evaluationsToPurge) && $purgedEvaluations < $max);
    }

    public function purgeEvaluationsWithoutProducts(int $batchSize, int $max): void
    {
        $query = <<<SQL
SELECT evaluations.id
FROM pimee_data_quality_insights_criteria_evaluation AS evaluations
    LEFT JOIN pim_catalog_product AS product ON product.id = evaluations.product_id
    WHERE product.id IS NULL
LIMIT $batchSize
SQL;

        $purgedEvaluations = 0;
        do {
            $evaluationsToPurge = $this->db->executeQuery($query)->fetchAll(\PDO::FETCH_COLUMN);
            $this->deleteByIds($evaluationsToPurge);
            $purgedEvaluations += count($evaluationsToPurge);
        } while (!empty($evaluationsToPurge) && $purgedEvaluations < $max);
    }

    private function deleteByIds(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $query = <<<SQL
DELETE FROM pimee_data_quality_insights_criteria_evaluation WHERE id IN (:ids)
SQL;

        $this->db->executeQuery(
            $query,
            ['ids' => $ids],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );
    }
}
