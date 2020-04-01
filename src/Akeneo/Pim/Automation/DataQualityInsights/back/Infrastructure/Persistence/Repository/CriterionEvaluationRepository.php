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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DeadlockException;

class CriterionEvaluationRepository
{
    /** @var Connection */
    protected $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function createCriterionEvaluationsForProducts(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $queryFormat = <<<SQL
INSERT IGNORE INTO pimee_data_quality_insights_criteria_evaluation 
    (id, criterion_code, product_id, created_at, status, pending) VALUES %s;
SQL;

        $this->createFromSqlQueryFormat($queryFormat, $criteriaEvaluations);
    }

    public function createCriterionEvaluationsForProductModels(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $queryFormat = <<<SQL
INSERT IGNORE INTO pimee_data_quality_insights_product_model_criteria_evaluation 
    (id, criterion_code, product_id, created_at, status, pending) VALUES %s;
SQL;

        $this->createFromSqlQueryFormat($queryFormat, $criteriaEvaluations);
    }

    public function updateCriterionEvaluationsForProducts(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $queryFormat = <<<SQL
UPDATE pimee_data_quality_insights_criteria_evaluation
SET criterion_code = :%s, product_id = :%s, started_at = :%s, ended_at = :%s, status = :%s, pending = :%s, result = :%s
WHERE id = :%s;
SQL;
        $this->updateFromSqlQueryFormat($queryFormat, $criteriaEvaluations);
    }

    public function updateCriterionEvaluationsForProductModels(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $queryFormat = <<<SQL
UPDATE pimee_data_quality_insights_product_model_criteria_evaluation
SET criterion_code = :%s, product_id = :%s, started_at = :%s, ended_at = :%s, status = :%s, pending = :%s, result = :%s
WHERE id = :%s;
SQL;
        $this->updateFromSqlQueryFormat($queryFormat, $criteriaEvaluations);
    }

    private function createFromSqlQueryFormat(string $queryFormat, Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        if (0 === $criteriaEvaluations->count()) {
            return;
        }

        $valuesPlaceholders = [];
        $queryParametersValues = [];
        $queryParametersTypes = [];
        foreach ($criteriaEvaluations as $index => $criterionEvaluation) {
            $id = sprintf('id_%d', $index);
            $productId = sprintf('productId_%d', $index);
            $criterionCode = sprintf('criterionCode_%d', $index);
            $createdAt = sprintf('createdAt_%d', $index);
            $status = sprintf('status_%d', $index);
            $pending = sprintf('pending_%d', $index);

            $valuesPlaceholders[] = sprintf('(:%s, :%s, :%s, :%s, :%s, :%s)', $id, $criterionCode, $productId, $createdAt, $status, $pending);

            $queryParametersValues[$id] = strval($criterionEvaluation->getId());
            $queryParametersValues[$criterionCode] = strval($criterionEvaluation->getCriterionCode());
            $queryParametersValues[$productId] = $criterionEvaluation->getProductId()->toInt();
            $queryParametersValues[$createdAt] = $criterionEvaluation->getCreatedAt()->format(Clock::TIME_FORMAT);
            $queryParametersValues[$status] = $criterionEvaluation->getStatus();
            $queryParametersValues[$pending] = $criterionEvaluation->isPending() ? 1 : null;

            $queryParametersTypes[$productId] = \PDO::PARAM_INT;
            $queryParametersTypes[$pending] = \PDO::PARAM_INT;
        }

        $valuesPlaceholders = implode(', ', $valuesPlaceholders);
        $query = sprintf($queryFormat, $valuesPlaceholders);

        $success = false;
        $retry = 0;

        while (!$success) {
            try {
                $this->dbConnection->executeQuery($query, $queryParametersValues, $queryParametersTypes);
                $success = true;
            } catch (DeadlockException $e) {
                $retry++;
                if ($retry == 5) {
                    $this->executeWithLock($query, $queryParametersValues, $queryParametersTypes);
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
    private function executeWithLock(string $query, array $queryParametersValues, array $queryParametersTypes): void
    {
        $value = $this->dbConnection->executeQuery('SELECT @@autocommit')->fetch();
        if (!isset($value['@@autocommit']) && ((int) $value['@@autocommit'] !== 1 || (int) $value['@@autocommit'] !== 0)) {
            throw new \LogicException('Error when getting autocommit parameter from Mysql.');
        }

        $formerAutocommitValue = (int) $value['@@autocommit'];
        try {
            $this->dbConnection->executeQuery('SET autocommit=0');
            $this->dbConnection->executeQuery('SET foreign_key_checks=0');
            $this->dbConnection->executeQuery('LOCK TABLES pimee_data_quality_insights_criteria_evaluation WRITE');
            $this->dbConnection->executeQuery($query, $queryParametersValues, $queryParametersTypes);
            $this->dbConnection->executeQuery('COMMIT');
        } finally {
            $this->dbConnection->executeQuery('UNLOCK TABLES');
            $this->dbConnection->executeQuery('SET foreign_key_checks=1');
            $this->dbConnection->executeQuery(sprintf('SET autocommit=%d', $formerAutocommitValue));
        }
    }

    private function updateFromSqlQueryFormat(string $sqlQueryFormat, Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        if (0 === $criteriaEvaluations->count()) {
            return;
        }

        $queries = [];
        $queryParametersValues = [];
        $queryParametersTypes = [];

        /** @var Write\CriterionEvaluation $criterionEvaluation */
        foreach ($criteriaEvaluations as $index => $criterionEvaluation) {
            $id = sprintf('id_%d', $index);
            $productId = sprintf('productId_%d', $index);
            $criterionCode = sprintf('criterionCode_%d', $index);
            $startedAt = sprintf('startedAt_%d', $index);
            $endedAt = sprintf('endedAt_%d', $index);
            $status = sprintf('status_%d', $index);
            $pending = sprintf('pending_%d', $index);
            $result = sprintf('result_%d', $index);

            $queries[] = sprintf($sqlQueryFormat, $criterionCode, $productId, $startedAt, $endedAt, $status, $pending, $result, $id);

            $queryParametersValues[$id] = strval($criterionEvaluation->getId());
            $queryParametersValues[$criterionCode] = strval($criterionEvaluation->getCriterionCode());
            $queryParametersValues[$productId] = $criterionEvaluation->getProductId()->toInt();
            $queryParametersValues[$startedAt] = $this->formatDate($criterionEvaluation->getStartedAt());
            $queryParametersValues[$endedAt] = $this->formatDate($criterionEvaluation->getEndedAt());
            $queryParametersValues[$status] = $criterionEvaluation->getStatus();
            $queryParametersValues[$pending] = $criterionEvaluation->isPending() ? 1 : null;
            $queryParametersValues[$result] = $this->formatCriterionEvaluationResult($criterionEvaluation->getResult());

            $queryParametersTypes[$productId] = \PDO::PARAM_INT;
            $queryParametersTypes[$pending] = \PDO::PARAM_INT;
        }

        $this->dbConnection->executeQuery(implode("\n", $queries), $queryParametersValues, $queryParametersTypes);
    }


    private function formatCriterionEvaluationResult(?Write\CriterionEvaluationResult $criterionEvaluationResult): ?string
    {
        return null !== $criterionEvaluationResult ? json_encode([
            'rates' => $criterionEvaluationResult->getRates()->toArrayInt(),
            'status' => $criterionEvaluationResult->getStatus()->toArrayString(),
            'data' => $criterionEvaluationResult->getDataToArray(),
        ]) : null;
    }

    private function formatDate(?\DateTimeImmutable $date): ?string
    {
        return null !== $date ? $date->format(Clock::TIME_FORMAT) : null;
    }
}
