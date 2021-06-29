<?php

declare(strict_types=1);

namespace Akeneo\Test\Tool\Integration\Batch\Persistence\Sql;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\GetJobExecutionIds;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class GetJobExecutionIdsIntegration extends TestCase {

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    public function tests_that_it_returns_the_older_than_days_ids() {

        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->olderThanDays(5);
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [1, 2];
        $this->assertEquals($expectedIds, $ids);
    }

    public function tests_that_it_returns_all_ids () {

        $this->loadFixtures();

        /** @var GetJobExecutionIds $getJobExecutionIds */
        $getJobExecutionIds = $this->get(GetJobExecutionIds::class);
        $result = $getJobExecutionIds->all();
        $ids = $result->fetchAll(FetchMode::COLUMN);
        $expectedIds = [1, 2, 3];
        $this->assertEquals($expectedIds, $ids);
    }

    private function loadFixtures() {

        $insertJobInstanceQuery = <<<SQL
            INSERT INTO akeneo_batch_job_instance (id, code, job_name, status, connector, raw_parameters, type)
            VALUES 
            (1, 'test', '', 0, '', '', '')
SQL;

        $this->getConnection()->executeQuery($insertJobInstanceQuery);

        $insertJobExecutionQuery = <<<SQL
            INSERT INTO akeneo_batch_job_execution (id, job_instance_id, create_time, status, raw_parameters) 
            VALUES 
            (1, 1, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}'), 
            (2, 1, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}'), 
            (3, 1, DATE_SUB(NOW(), INTERVAL 3 day), 1, '{}')
SQL;

        $this->getConnection()->executeQuery($insertJobExecutionQuery);
    }
}
