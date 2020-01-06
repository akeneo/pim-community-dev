<?php
declare(strict_types=1);

namespace Pimee\Upgrade\Schema\Tests;


use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class Version_4_0_20191210100000_data_quality_insights_add_evaluation_job_Integration extends TestCase
{

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_adds_data_quality_insights_evaluation_job()
    {
        $resultUp = $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel())
        );
        self::assertEquals(0, $resultUp->getCommandStatus(), \json_encode($resultUp->getCommandOutput()));

        $stmt = $this->get('database_connection')->executeQuery(
            'SELECT code FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME]
        );
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        self::assertIsArray($result);
        self::assertEquals(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME, $result['code']);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
