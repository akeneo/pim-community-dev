<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;


use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;

final class LogMetricsTasklet implements TaskletInterface
{
    /** @var Connection */
    private $db;

    /** @var LoggerInterface */
    private $logger;

    /** @var LoggerInterface */
    private $qualityLogger;

    public function __construct(Connection $db, LoggerInterface $logger, LoggerInterface $qualityLogger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->qualityLogger = $qualityLogger;
    }

    /**
     * @inheritDoc
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        try {
            $metrics = [
                'productTableSize' => $this->getTableSize('pim_catalog_product'),
                'productEvaluationTableSize' => $this->getTableSize('pim_data_quality_insights_product_criteria_evaluation'),
                'productModelTableSize' => $this->getTableSize('pim_catalog_product_model'),
                'productModelEvaluationTableSize' => $this->getTableSize('pim_data_quality_insights_product_model_criteria_evaluation'),
            ];

            $this->qualityLogger->error('Data Quality Insights periodic metrics log', $metrics);

        } catch (\Exception $exception) {
            $this->stepExecution->addFailureException($exception);
            $this->logger->error('Unable to log data quality insights metrics', [
                'step_execution_id' => $this->stepExecution->getId(),
                'message' => $exception->getMessage()
            ]);
        }
    }

    private function getTableSize(string $tableName): int
    {
        //FIX ME: can we force the ANALYZE table once a day like this ?
        $this->db->executeQuery(sprintf('ANALYZE TABLE %s', $tableName))->fetchAll();

        $query = <<<SQL
SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024)
FROM information_schema.TABLES WHERE TABLE_NAME = :tableName
SQL;
        try{
            $size = $this->db->executeQuery($query, ['tableName' => $tableName],)->fetchColumn();
            return intval($size);
        }catch (\Exception $exception)
        {
            //Maybe the user does not have the rights to query information_schema.
        }

        return -1;
    }
}
