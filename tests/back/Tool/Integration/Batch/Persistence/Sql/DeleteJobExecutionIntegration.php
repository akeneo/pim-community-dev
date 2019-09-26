<?php

declare(strict_types=1);

namespace Akeneo\Test\Tool\Integration\Batch\Persistence\Sql;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\DeleteJobExecution;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DeleteJobExecutionIntegration extends TestCase
{
    public function test_that_it_deletes_all_jobs_older_than_one_day_except_the_most_recent_one_for_each_job_instance()
    {
        $this->launchJob();
        $this->launchJob();
        $numberOfJobs = (int) $this->getConnection()->executeQuery('SELECT COUNT(*) as number_of_jobs FROM akeneo_batch_job_execution')->fetch()['number_of_jobs'];
        Assert::assertSame(2, $numberOfJobs);

        $this->getConnection()->executeUpdate('UPDATE akeneo_batch_job_execution SET create_time = Date_ADD(end_time, INTERVAL -10 day)');

        $deleteJobExecution = $this->getDeleteQuery();
        $deleteJobExecution->olderThanDays(1);

        $numberOfJobs = (int) $this->getConnection()->executeQuery('SELECT COUNT(*) as number_of_jobs FROM akeneo_batch_job_execution')->fetch()['number_of_jobs'];
        Assert::assertSame(1, $numberOfJobs);

    }

    public function test_that_it_deletes_all_jobs()
    {
        $this->launchJob();
        $this->launchJob();
        $numberOfJobs = (int) $this->getConnection()->executeQuery('SELECT COUNT(*) as number_of_jobs FROM akeneo_batch_job_execution')->fetch()['number_of_jobs'];
        Assert::assertSame(2, $numberOfJobs);

        $this->getConnection()->executeUpdate('UPDATE akeneo_batch_job_execution SET create_time = Date_ADD(end_time, INTERVAL -10 day)');

        $deleteJobExecution = $this->getDeleteQuery();
        $deleteJobExecution->all();

        $numberOfJobs = (int) $this->getConnection()->executeQuery('SELECT COUNT(*) as number_of_jobs FROM akeneo_batch_job_execution')->fetch()['number_of_jobs'];
        Assert::assertSame(0, $numberOfJobs);

    }

    private function getDeleteQuery(): DeleteJobExecution
    {
        return $this->get('akeneo_batch.delete_job_execution');
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
