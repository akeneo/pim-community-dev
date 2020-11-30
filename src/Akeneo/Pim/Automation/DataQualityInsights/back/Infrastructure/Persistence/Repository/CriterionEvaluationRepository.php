<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DeadlockException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriterionEvaluationRepository
{
    private Connection $dbConnection;

    private TransformCriterionEvaluationResultCodes $transformCriterionEvaluationResult;

    public function __construct(Connection $dbConnection, TransformCriterionEvaluationResultCodes $transformCriterionEvaluationResult)
    {
        $this->dbConnection = $dbConnection;
        $this->transformCriterionEvaluationResult = $transformCriterionEvaluationResult;
    }

    public function createCriterionEvaluationsForProducts(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $queryFormat = <<<SQL
INSERT INTO pim_data_quality_insights_product_criteria_evaluation
    (product_id, criterion_code, status) VALUES (:%s, :%s, :%s)
ON DUPLICATE KEY UPDATE status = :%s;
SQL;

        $this->createFromSqlQueryFormat($queryFormat, $criteriaEvaluations);
    }

    public function createCriterionEvaluationsForProductModels(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $queryFormat = <<<SQL
INSERT INTO pim_data_quality_insights_product_model_criteria_evaluation
    (product_id, criterion_code, status) VALUES (:%s, :%s, :%s)
ON DUPLICATE KEY UPDATE status = :%s;
SQL;

        $this->createFromSqlQueryFormat($queryFormat, $criteriaEvaluations);
    }

    public function updateCriterionEvaluationsForProducts(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $queryFormat = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation
SET evaluated_at = :%s, status = :%s, result = :%s
WHERE product_id = :%s AND criterion_code = :%s;
SQL;
        $this->updateFromSqlQueryFormat($queryFormat, $criteriaEvaluations);
    }

    public function updateCriterionEvaluationsForProductModels(Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        $queryFormat = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation
SET evaluated_at = :%s, status = :%s, result = :%s
WHERE product_id = :%s AND criterion_code = :%s;
SQL;
        $this->updateFromSqlQueryFormat($queryFormat, $criteriaEvaluations);
    }

    private function createFromSqlQueryFormat(string $queryFormat, Write\CriterionEvaluationCollection $criteriaEvaluations): void
    {
        if (0 === $criteriaEvaluations->count()) {
            return;
        }

        $queries = [];
        $queryParametersValues = [];
        $queryParametersTypes = [];
        foreach ($criteriaEvaluations as $index => $criterionEvaluation) {
            $productId = sprintf('productId_%d', $index);
            $criterionCode = sprintf('criterionCode_%d', $index);
            $status = sprintf('status_%d', $index);

            $queries[] = sprintf($queryFormat, $productId, $criterionCode, $status, $status);

            $queryParametersValues[$criterionCode] = strval($criterionEvaluation->getCriterionCode());
            $queryParametersValues[$productId] = $criterionEvaluation->getProductId()->toInt();
            $queryParametersValues[$status] = $criterionEvaluation->getStatus();

            $queryParametersTypes[$productId] = \PDO::PARAM_INT;
        }

        $query = implode("\n", $queries);
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
            $this->dbConnection->executeQuery('LOCK TABLES pim_data_quality_insights_product_criteria_evaluation WRITE');
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
            $productId = sprintf('productId_%d', $index);
            $criterionCode = sprintf('criterionCode_%d', $index);
            $evaluatedAt = sprintf('evaluatedAt_%d', $index);
            $status = sprintf('status_%d', $index);
            $result = sprintf('result_%d', $index);

            $queries[] = sprintf($sqlQueryFormat, $evaluatedAt, $status, $result, $productId, $criterionCode);

            $queryParametersValues[$criterionCode] = strval($criterionEvaluation->getCriterionCode());
            $queryParametersValues[$productId] = $criterionEvaluation->getProductId()->toInt();
            $queryParametersValues[$evaluatedAt] = $this->formatDate($criterionEvaluation->getEvaluatedAt());
            $queryParametersValues[$status] = $criterionEvaluation->getStatus();
            $queryParametersValues[$result] = $this->formatCriterionEvaluationResult($criterionEvaluation->getResult());

            $queryParametersTypes[$productId] = \PDO::PARAM_INT;
        }

        $this->dbConnection->executeQuery(implode("\n", $queries), $queryParametersValues, $queryParametersTypes);
    }


    private function formatCriterionEvaluationResult(?Write\CriterionEvaluationResult $criterionEvaluationResult): ?string
    {
        if (null === $criterionEvaluationResult) {
            return null;
        }

        $formattedCriterionEvaluationResult = [
            'rates' => $criterionEvaluationResult->getRates()->toArrayInt(),
            'status' => $criterionEvaluationResult->getStatus()->toArrayString(),
            'data' => $criterionEvaluationResult->getDataToArray(),
        ];

        $formattedCriterionEvaluationResult = $this->transformCriterionEvaluationResult->transformToIds($formattedCriterionEvaluationResult);

        return json_encode($formattedCriterionEvaluationResult);
    }

    private function formatDate(?\DateTimeImmutable $date): ?string
    {
        return null !== $date ? $date->format(Clock::TIME_FORMAT) : null;
    }
}
