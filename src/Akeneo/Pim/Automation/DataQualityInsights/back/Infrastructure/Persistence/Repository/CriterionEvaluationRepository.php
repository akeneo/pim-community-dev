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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Statement;

final class CriterionEvaluationRepository implements CriterionEvaluationRepositoryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
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
    private function executeWithLock(Statement $statement): void
    {
        $value = $this->db->executeQuery('SELECT @@autocommit')->fetch();
        if (!isset($value['@@autocommit']) && ((int) $value['@@autocommit'] !== 1 || (int) $value['@@autocommit'] !== 0)) {
            throw new \LogicException('Error when getting autocommit parameter from Mysql.');
        }

        $formerAutocommitValue = (int) $value['@@autocommit'];
        try {
            $this->db->executeQuery('SET autocommit=0');
            $this->db->executeQuery('SET foreign_key_checks=0');
            $this->db->executeQuery('LOCK TABLES pimee_data_quality_insights_criteria_evaluation WRITE');
            $statement->execute();
            $this->db->executeQuery('COMMIT');
        } finally {
            $this->db->executeQuery('UNLOCK TABLES');
            $this->db->executeQuery('SET foreign_key_checks=1');
            $this->db->executeQuery(sprintf('SET autocommit=%d', $formerAutocommitValue));
        }
    }

    public function update(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $sql = <<<'SQL'
UPDATE pimee_data_quality_insights_criteria_evaluation
SET criterion_code = ?, product_id = ?, created_at = ?, started_at = ?, ended_at = ?, status = ?, pending = ?, result = ?
WHERE id = ?
SQL;

        $queries = implode('; ', array_fill(0, $criteriaEvaluations->count(), $sql));
        $statement = $this->db->prepare($queries);

        $valuePlaceholderIndex = 1;
        /** @var Write\CriterionEvaluation $criterionEvaluation */
        foreach ($criteriaEvaluations as $criterionEvaluation) {
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

            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getCriterionCode());
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getProductId()->toInt(), \PDO::PARAM_INT);
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getCreatedAt()->format(Clock::TIME_FORMAT));
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getStartedAt() instanceof \DateTimeImmutable ? $criterionEvaluation->getStartedAt()->format(Clock::TIME_FORMAT) : null);
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getEndedAt() instanceof \DateTimeImmutable ? $criterionEvaluation->getEndedAt()->format(Clock::TIME_FORMAT) : null);
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getStatus());
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->isPending() ? 1 : null, \PDO::PARAM_INT);
            $statement->bindValue($valuePlaceholderIndex++, $result);
            $statement->bindValue($valuePlaceholderIndex++, $criterionEvaluation->getId());
        }

        $statement->execute();
    }

    public function purgeUntil(\DateTimeImmutable $date): void
    {
        $query = <<<SQL
DELETE old_evaluations
FROM pimee_data_quality_insights_criteria_evaluation AS old_evaluations
INNER JOIN pimee_data_quality_insights_criteria_evaluation AS younger_evaluations
    ON younger_evaluations.product_id = old_evaluations.product_id
    AND younger_evaluations.criterion_code = old_evaluations.criterion_code
    AND younger_evaluations.created_at > old_evaluations.created_at
WHERE old_evaluations.created_at < :purge_date
SQL;

        $this->db->executeQuery(
            $query,
            ['purge_date' => $date->format('Y-m-d 00:00:00')]
        );
    }
}
