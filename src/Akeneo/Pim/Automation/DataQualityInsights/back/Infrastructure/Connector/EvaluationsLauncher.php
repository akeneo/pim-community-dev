<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\AnotherJobStillRunningException;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\RunUniqueProcessJob;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluationsLauncher
{
    public function __construct(
        private RunUniqueProcessJob $runUniqueProcessJob,
        private FeatureFlag $featureFlag,
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function run(): void
    {
        if (!$this->featureFlag->isEnabled()) {
            $this->logger->info('DQI evaluations launcher: Data Quality Insights feature is disabled');

            return;
        }

        if ($this->hasStartedMigration()) {
            $this->logger->info('DQI evaluations launcher: There is a migration in progress, skip');

            return;
        }

        try {
            $this->runUniqueProcessJob->run(
                'data_quality_insights_evaluations',
                function (?JobExecution $lastJobExecution) {
                    return [];
                }
            );
        } catch (AnotherJobStillRunningException $e) {
            $this->logger->error(sprintf('DQI evaluations launcher error: %s', $e->getMessage()));

            exit(0);
        }

        $this->logger->info('DQI evaluations succesfully executed');
    }

    private function hasStartedMigration(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1
                FROM pim_one_time_task
                WHERE code=:code
                AND status=:status
                LIMIT 1
            ) AS missing
        SQL;

        return (bool)$this->connection->fetchOne($sql, [
            ':code' => 'pim:product:migrate-to-uuid',
            ':status' => 'started',
        ]);
    }
}
