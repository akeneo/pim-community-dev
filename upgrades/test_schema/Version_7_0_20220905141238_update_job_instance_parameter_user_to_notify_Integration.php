<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;

final class Version_7_0_20220905141238_update_job_instance_parameter_user_to_notify_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220905141238_update_job_instance_parameter_user_to_notify';

    private Connection $connection;
    private JobInstanceRepository $jobInstanceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
    }

    public function test_it_is_idempotent(): void {
        $this->createJob('my_job', 'admin');
        $this->createJob('other_job', null);

        $this->assertFalse($this->jobIsMigrated('my_job', ['admin']));
        $this->assertFalse($this->jobIsMigrated('other_job', []));

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL, true);

        $this->assertTrue($this->jobIsMigrated('my_job', ['admin']));
        $this->assertTrue($this->jobIsMigrated('other_job', []));
    }

    public function test_it_changes_user_to_notify_for_job_instance(): void
    {
        $this->createJob('my_job', 'admin');
        $this->createJob('other_job', null);

        $this->assertFalse($this->jobIsMigrated('my_job', ['admin']));
        $this->assertFalse($this->jobIsMigrated('other_job', []));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->jobIsMigrated('my_job', ['admin']));
        $this->assertTrue($this->jobIsMigrated('other_job', []));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createJob(string $jobCode, ?string $userToNotify): void
    {
        $rawParameters = [
            'storage' => [
                'type' => 'local',
                'file_path' => '/tmp/product.xlsx',
            ],
            'withHeader' => true,
            'uploadAllowed' => true,
            'invalid_items_file_format' => 'xlsx',
            'user_to_notify' => $userToNotify,
            'is_user_authenticated' => false,
            'decimalSeparator' => '.',
            'dateFormat' => 'yyyy-MM-dd',
            'enabled' => true,
            'categoriesColumn' => 'categories',
            'familyColumn' => 'family',
            'groupsColumn' => 'groups',
            'enabledComparison' => true,
            'realTimeVersioning' => true,
            'convertVariantToSimple' => false,
        ];

        $this->connection->executeStatement(
            'DELETE FROM akeneo_batch_job_instance WHERE code = :job_code',
            [
                'job_code' => $jobCode,
            ]
        );

        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	(:job_code, :job_code, :job_code, 0, 'Akeneo CSV Connector', :raw_parameters, 'import');
SQL;

        $this->connection->executeStatement($sql, [
            'job_code' => $jobCode,
            'raw_parameters' => serialize($rawParameters),
        ]);
    }

    private function jobIsMigrated(string $jobCode, array $usersToNotify): bool
    {
        $this->jobInstanceRepository->clear();

        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByCode($jobCode);
        $rawParameters = $jobInstance->getRawParameters();

        return !array_key_exists('user_to_notify', $rawParameters)
            && array_key_exists('users_to_notify', $rawParameters)
            && $rawParameters['users_to_notify'] === $usersToNotify;
    }
}
