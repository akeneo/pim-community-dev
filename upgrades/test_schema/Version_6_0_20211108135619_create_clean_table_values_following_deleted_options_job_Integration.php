<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20211108135619_create_clean_table_values_following_deleted_options_job_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211108135619_create_clean_table_values_following_deleted_options_job';
    private const JOB_CODE = 'clean_table_values_following_deleted_options';

    public function test_it_creates_the_job_if_needed(): void
    {
        $this->removeJob();
        self::assertFalse($this->assertJobExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        self::assertTrue($this->assertJobExists());
    }

    public function test_it_does_not_create_the_job_if_already_exists(): void
    {
        self::assertTrue($this->assertJobExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        self::assertTrue($this->assertJobExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function removeJob(): void
    {
        $this->get('database_connection')->executeQuery(
            'DELETE FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => self::JOB_CODE]
        );
    }

    private function assertJobExists(): bool
    {
        $result = $this->get('database_connection')->executeQuery(
            'SELECT id FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => self::JOB_CODE]
        )->fetchOne();

        return false !== $result;
    }
}
