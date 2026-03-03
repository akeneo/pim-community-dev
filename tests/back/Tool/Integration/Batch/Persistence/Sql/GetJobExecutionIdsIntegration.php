<?php

declare(strict_types=1);

namespace Akeneo\Test\Tool\Integration\Batch\Persistence\Sql;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\GetJobExecutionIds;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class GetJobExecutionIdsIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    public function tests_that_it_returns_the_older_than_days_ids()
    {
        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->olderThanDays(5, [], null);
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [1, 2, 4, 5];
        $this->assertEquals($expectedIds, $ids);
    }

    public function tests_that_it_returns_the_older_than_days_ids_for_given_instance_codes()
    {
        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->olderThanDays(5, ['csv_product_model_export'], null);
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [4, 5];
        $this->assertEquals($expectedIds, $ids);
    }

    public function tests_that_it_returns_the_older_than_days_ids_for_given_status()
    {
        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->olderThanDays(5, [], new BatchStatus(BatchStatus::FAILED));
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [2, 5];
        $this->assertEquals($expectedIds, $ids);
    }

    public function tests_that_it_returns_the_older_than_days_ids_for_given_instance_code_and_status()
    {
        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->olderThanDays(5, ['csv_product_model_export'], new BatchStatus(BatchStatus::FAILED));
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [5];
        $this->assertEquals($expectedIds, $ids);
    }

    public function tests_that_it_returns_all_ids()
    {
        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->all([], null);
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [1, 2, 3, 4, 5, 6];
        $this->assertEquals($expectedIds, $ids);
    }

    public function tests_that_it_returns_all_ids_for_given_instance_code()
    {
        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->all(['csv_product_model_export'], null);
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [4, 5, 6];
        $this->assertEquals($expectedIds, $ids);
    }

    public function tests_that_it_returns_all_ids_for_given_status()
    {
        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->all([], new BatchStatus(BatchStatus::FAILED));
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [2, 5];
        $this->assertEquals($expectedIds, $ids);
    }

    private function loadFixtures()
    {
        $insertJobInstanceQuery = <<<SQL
            INSERT INTO akeneo_batch_job_instance (id, code, job_name, status, connector, raw_parameters, type)
            VALUES 
            (1, 'csv_product_export', '', 0, '', '', ''),
            (2, 'csv_product_model_export', '', 0, '', '', '')
SQL;

        $this->getConnection()->executeQuery($insertJobInstanceQuery);

        $insertJobExecutionQuery = <<<SQL
            INSERT INTO akeneo_batch_job_execution (id, job_instance_id, create_time, status, raw_parameters) 
            VALUES 
            (1, 1, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}'), 
            (2, 1, DATE_SUB(NOW(), INTERVAL 10 day), 6, '{}'), 
            (3, 1, DATE_SUB(NOW(), INTERVAL 3 day), 1, '{}'),
            (4, 2, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}'),
            (5, 2, DATE_SUB(NOW(), INTERVAL 10 day), 6, '{}'),
            (6, 2, DATE_SUB(NOW(), INTERVAL 3 day), 1, '{}')
SQL;

        $this->getConnection()->executeQuery($insertJobExecutionQuery);
    }
}
