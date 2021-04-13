<?php

declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;
use PHPUnit\Framework\Assert;

class Version_6_0_20210402085432_add_mass_delete_records_job_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210402085432_add_mass_delete_records_job';

    public function test_it_adds_a_primary_key()
    {
        $this->removeJobInstance();
        Assert::assertNull($this->getJobInstance());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertInstanceOf(JobInstance::class, $this->getJobInstance());
    }

    private function removeJobInstance()
    {
        $this->get('database_connection')->executeUpdate(
            <<<SQL
            DELETE jobinstance.*, permissions.*
            FROM akeneo_batch_job_instance jobinstance
            LEFT JOIN pimee_security_job_profile_access permissions on jobinstance.id = permissions.job_profile_id
            WHERE jobinstance.code = 'reference_entity_mass_delete_records';
SQL
        );
    }

    private function getJobInstance(): ?JobInstance
    {
        return $this->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('reference_entity_mass_delete_records');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
