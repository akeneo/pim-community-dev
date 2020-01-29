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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Test\Integration\TestCase;

final class Version_4_0_20200129102033_franklin_insights_schedule_job_to_push_structure_and_products_Integration extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_does_not_launch_the_job_if_Franklin_is_not_activated()
    {
        $this->assertFalse($this->isJobScheduled());
        $this->runMigration($this->getMigrationLabel());
        $this->assertFalse($this->isJobScheduled());
    }

    public function test_it_launches_the_job_if_Franklin_is_activated()
    {
        $this->runMigration('_4_0_20200128163617_franklin_insights_add_job_push_structure_and_products');
        $this->activateFranklin();
        $this->assertFalse($this->isJobScheduled());
        $this->runMigration($this->getMigrationLabel());
        $this->assertTrue($this->isJobScheduled());
    }

    private function activateFranklin(): void
    {
        $this->get('pim_catalog.command_launcher')->executeForeground('pimee:franklin-insights:init-franklin-user');

        $configuration = new Configuration();
        $configuration->setToken(new Token('a_token'));

        $this->get('akeneo.pim.automation.franklin_insights.repository.configuration')->save($configuration);
    }

    private function isJobScheduled(): bool
    {
        $query = <<<SQL
SELECT 1
FROM akeneo_batch_job_execution_queue AS job_queue
    INNER JOIN akeneo_batch_job_execution AS job_execution ON job_execution.id = job_queue.job_execution_id
    INNER JOIN akeneo_batch_job_instance AS job ON job.id = job_execution.job_instance_id
WHERE job.code = :job_code
SQL;

        $stmt = $this->get('database_connection')->executeQuery(
            $query,
            ['job_code' => JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS]
        );

        return (bool) $stmt->fetchColumn();
    }

    private function runMigration(string $migrationLabel): void
    {
        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $migrationLabel);
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
