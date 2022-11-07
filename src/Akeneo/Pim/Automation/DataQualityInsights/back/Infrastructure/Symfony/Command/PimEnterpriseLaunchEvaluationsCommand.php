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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\AnotherJobStillRunningException;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\RunUniqueProcessJob;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\Evaluation;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PimEnterpriseLaunchEvaluationsCommand extends Command
{
    protected static $defaultName = 'pim:data-quality-insights:evaluations';
    protected static $defaultDescription = 'Launch the evaluations of products and structure';

    public function __construct(
        private RunUniqueProcessJob $runUniqueProcessJob,
        private FeatureFlag $featureFlag,
        private Connection $connection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->featureFlag->isEnabled()) {
            $output->writeln('<info>Data Quality Insights feature is disabled</info>');
            return Command::SUCCESS;
        }

        if ($this->hasStartedMigration()) {
            $output->writeln('<info>There is a migration in progress, skip</info>');
            return Command::SUCCESS;
        }

        try {
            $this->runUniqueProcessJob->run('data_quality_insights_evaluations', function (?JobExecution $lastJobExecution) {
                return $this->getJobParameters($lastJobExecution);
            });
        } catch (AnotherJobStillRunningException $e) {
            exit(0);
        }

        return Command::SUCCESS;
    }

    private function getJobParameters(?JobExecution $lastJobExecution): array
    {
        $defaultEvaluationDate = new \DateTime(Evaluation::EVALUATED_SINCE_DEFAULT_TIME);

        $lastEvaluationDate = $defaultEvaluationDate;
        if (null !== $lastJobExecution) {
            $lastEvaluationDate = max($lastJobExecution->getStartTime(), $defaultEvaluationDate);
        }

        return [
            Evaluation::EVALUATED_SINCE_PARAMETER => $lastEvaluationDate->format(Evaluation::EVALUATED_SINCE_DATE_FORMAT)
        ];
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

        return (bool) $this->connection->fetchOne($sql, [
            'code' => 'pim:product:migrate-to-uuid',
            'status' => 'started',
        ]);
    }
}
