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

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

class Version_5_0_20201013071343_add_convert_simple_products_job_permissions_Integration extends TestCase
{
    private const MIGRATION_LABEL = '_5_0_20201013071343_add_convert_simple_products_job_permissions';
    private const JOB_CODE = 'convert_to_simple_products';

    use ExecuteMigrationTrait;

    /**
     * @test
     */
    public function it_grants_permissions_to_All_group()
    {
        $jobInstance = $this->getJobInstance();
        Assert::assertInstanceOf(JobInstance::class, $jobInstance);
        Assert::assertFalse($this->jobProfileAccessIsDefined());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->jobProfileAccessIsDefined());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('database_connection')->executeUpdate(
            <<<SQL
            DELETE permissions.*
            FROM akeneo_batch_job_instance jobinstance
            LEFT JOIN pimee_security_job_profile_access permissions on jobinstance.id = permissions.job_profile_id
            WHERE jobinstance.code = :code;
            SQL,
            ['code' => self::JOB_CODE]
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getJobInstance(): ?JobInstance
    {
        return $this->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('rule_engine_execute_rules');
    }

    private function jobProfileAccessIsDefined(): bool
    {
        $connection = $this->get('database_connection');
        $sql = <<<SQL
SELECT EXISTS (
    SELECT a.id
    FROM pimee_security_job_profile_access as a
        JOIN akeneo_batch_job_instance j ON j.id = a.job_profile_id
        JOIN oro_access_group g ON g.id = a.user_group_id
    WHERE j.code = :code AND g.name = 'All'
) AS is_existing
SQL;
        $result = $connection->executeQuery($sql, ['code' => self::JOB_CODE])->fetchColumn();

        return (bool) $result;
    }
}
