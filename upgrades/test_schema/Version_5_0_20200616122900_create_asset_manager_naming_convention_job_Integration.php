<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

class Version_5_0_20200616122900_create_asset_manager_naming_convention_job_Integration extends TestCase
{
    private const MIGRATION_LABEL = '_5_0_20200616122900_create_asset_manager_naming_convention_job';

    use ExecuteMigrationTrait;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('database_connection')->executeUpdate(<<<SQL
            DELETE jobinstance.*, permissions.*
            FROM akeneo_batch_job_instance jobinstance
            LEFT JOIN pimee_security_job_profile_access permissions on jobinstance.id = permissions.job_profile_id
            WHERE jobinstance.code = 'asset_manager_execute_naming_convention';
SQL
        );
    }

    /** @test */
    public function it_creates_a_rule_execution_job_instance()
    {
        Assert::assertNull($this->getJobInstance());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertInstanceOf(JobInstance::class, $this->getJobInstance());
    }

    private function getJobInstance(): ?JobInstance
    {
        return $this->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('asset_manager_execute_naming_convention');
    }
}
