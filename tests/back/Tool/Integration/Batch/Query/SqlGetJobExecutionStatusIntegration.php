<?php

declare(strict_types=1);

namespace Akeneo\Test\Tool\Integration\Batch\Query;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Query\SqlGetJobExecutionStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SqlGetJobExecutionStatusIntegration extends TestCase
{
    public function test_that_it_returns_the_execution_status_of_the_provided_job_id()
    {
        $this->launchJob();
        $jobExecution = $this->getJobExecution();

        $getJobExecutionStatus = $this->getQuery();
        $status = $getJobExecutionStatus->getByJobExecutionId((int) $jobExecution['id']);

        Assert::assertEquals(new BatchStatus(BatchStatus::COMPLETED), $status);
    }

    public function test_that_it_returns_null_when_no_job_is_found()
    {
        $getJobExecutionStatus = $this->getQuery();
        $status = $getJobExecutionStatus->getByJobExecutionId(-1);

        Assert::assertNull($status);
    }

    private function getQuery(): SqlGetJobExecutionStatus
    {
        return $this->get('akeneo_batch.query.get_job_execution_status');
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
