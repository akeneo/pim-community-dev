<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobInstanceInterface;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;

class DeleteJobInstanceTest extends IntegrationTestCase
{
    private Connection $connection;
    private DeleteJobInstanceInterface $deleteJobInstanceQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->deleteJobInstanceQuery = $this->get(DeleteJobInstanceInterface::class);
        $this->loadFixtures();
    }

    public function test_it_delete_job_by_codes(): void
    {
        $this->assertEqualsCanonicalizing([
            'a_job_instance_to_delete',
            'another_job_instance_to_delete',
            'a_job_instance_to_keep'
        ], $this->findAllJobInstanceCodes());

        $this->deleteJobInstanceQuery->byCodes(['a_job_instance_to_delete', 'another_job_instance_to_delete']);

        $this->assertEqualsCanonicalizing(['a_job_instance_to_keep'], $this->findAllJobInstanceCodes());
    }

    private function loadFixtures(): void
    {
        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_job_instance_to_delete',
            'job_name' => 'a_job_instance_to_delete',
            'label' => 'A job instance to delete',
            'type' => 'import',
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'another_job_instance_to_delete',
            'job_name' => 'another_job_instance_to_delete',
            'label' => 'Another job instance to delete',
            'type' => 'export',
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_job_instance_to_keep',
            'job_name' => 'a_job_instance_to_keep',
            'label' => 'Another job instance to keep',
            'type' => 'import',
        ]);
    }

    public function findAllJobInstanceCodes(): array
    {
        $sql = <<<SQL
SELECT `code` FROM akeneo_batch_job_instance
SQL;

        return $this->connection->executeQuery($sql)->fetchFirstColumn();
    }
}
