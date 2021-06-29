<?php

declare(strict_types=1);

namespace Akeneo\Test\Tool\Integration\Batch\Query;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Query\SqlUpdateJobExecutionStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SqlUpdateJobExecutionStatusIntegration extends TestCase
{
    public function test_that_it_updates_the_execution_status_of_the_provided_job_id_with_the_provided_status()
    {
        $this->launchJob();
        $jobExecution = $this->getJobExecution();

        $updateJobExecutionStatus = $this->getQuery();
        $updateJobExecutionStatus->updateByJobExecutionId((int) $jobExecution['id'], new BatchStatus(BatchStatus::STOPPING));

        $jobExecution = $this->getJobExecution();

        Assert::assertEquals(new BatchStatus(BatchStatus::STOPPING), new BatchStatus($jobExecution['status']));
        Assert::assertEquals((new BatchStatus(BatchStatus::STOPPING))->__toString(), $jobExecution['exit_code']);
    }

    private function getQuery(): SqlUpdateJobExecutionStatus
    {
        return $this->get('akeneo_batch.query.update_job_execution_status');
    }

    private function getJobExecution(): array
    {
        return $this->getConnection()->executeQuery('SELECT * from akeneo_batch_job_execution LIMIT 1')->fetch();
    }

    private function launchJob(array $arrayInput = []): BufferedOutput
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $defaultArrayInput = [
            'command'  => 'akeneo:batch:job',
            'code'     => 'csv_product_export',
        ];

        $arrayInput = array_merge($defaultArrayInput, $arrayInput);
        if (isset($arrayInput['--config'])) {
            $arrayInput['--config'] = json_encode($arrayInput['--config']);
        }

        $input = new ArrayInput($arrayInput);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output;
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
