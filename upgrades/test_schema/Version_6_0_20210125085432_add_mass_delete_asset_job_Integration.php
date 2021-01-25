<?php
declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

class Version_6_0_20210125085432_add_mass_delete_asset_job_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210125085432_add_mass_delete_asset_job';

    public function test_it_adds_a_primary_key()
    {
        Assert::assertNull($this->getJobInstance());
        Assert::assertFalse($this->jobProfileAccessIsDefined());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $jobInstance = $this->getJobInstance();
        Assert::assertInstanceOf(JobInstance::class, $jobInstance);
        Assert::assertTrue($this->jobProfileAccessIsDefined());
    }

    private function getJobInstance(): ?JobInstance
    {
        return $this->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('asset_manager_mass_delete_assets');
    }

    protected function isColumnAPrimaryKey(string $table, string $column): bool
    {
        return 1 === $this->get('database_connection')
                ->executeQuery(sprintf('SHOW KEYS FROM %s WHERE Key_name = "PRIMARY" and Column_name = "%s";', $table, $column))
                ->rowCount();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
