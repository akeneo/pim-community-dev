<?php

declare(strict_types=1);

namespace Akeneo\Test\Tool\Integration\Batch\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Query\SqlGetPausedJobExecutionIds;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class SqlGetPausedJobExecutionIdsIntegration extends TestCase
{
    public function test_that_it_returns_empty_array()
    {
        $getPausedJobExecutionIds = $this->getQuery();
        $expected = [];
        Assert::assertEquals($expected, $getPausedJobExecutionIds->all());
    }
    public function test_that_it_returns_paused_job_execution_ids()
    {
        $this->createJobExecutions();

        $getPausedJobExecutionIds = $this->getQuery();
        $expected = [1, 3];
        Assert::assertEquals($expected, $getPausedJobExecutionIds->all());
    }

    private function createJobExecutions(): void
    {
        $insertJobInstanceQuery = <<<SQL
            INSERT INTO akeneo_batch_job_instance (id, code, job_name, status, connector, raw_parameters, type)
            VALUES 
            (1, 'a_job', '', 0, '', '', '')
SQL;

        $this->getConnection()->executeQuery($insertJobInstanceQuery);

        $insertJobExecutionQuery = <<<SQL
            INSERT INTO akeneo_batch_job_execution (id, job_instance_id, status, raw_parameters) 
            VALUES 
            (1, 1, 10, '{}'), 
            (2, 1, 1, '{}'), 
            (3, 1, 10, '{}')
SQL;

        $this->getConnection()->executeQuery($insertJobExecutionQuery);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getQuery(): SqlGetPausedJobExecutionIds
    {
        return $this->get('akeneo_batch.query.get_paused_job_execution_ids');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
