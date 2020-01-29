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

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Test\Integration\TestCase;

final class Version_4_0_20200128163617_franklin_insights_add_job_push_structure_and_products_Integration extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_the_job_to_push_structure_and_products()
    {
        $this->assertFalse($this->isJobExist());
        $this->runMigration();
        $this->assertTrue($this->isJobExist());
    }

    private function isJobExist(): bool
    {
        $query = <<<SQL
SELECT 1
FROM akeneo_batch_job_instance
WHERE code = :job_code
SQL;

        $stmt = $this->get('database_connection')->executeQuery(
            $query,
            ['job_code' => JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS]
        );

        return (bool) $stmt->fetchColumn();
    }

    private function runMigration(): void
    {
        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel());
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function getMigrationLabel()
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
