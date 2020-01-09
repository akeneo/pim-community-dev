<?php
declare(strict_types=1);

namespace Pimee\Upgrade\Schema\Tests;


use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class Version_4_0_20191023164418_data_quality_insights_create_tables_Integration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_data_quality_insights_tables()
    {
        $resultUp = $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --down -n', $this->getMigrationLabel())
        );
        self::assertEquals(0, $resultUp->getCommandStatus(), \json_encode($resultUp->getCommandOutput()));

        $resultUp = $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel())
        );
        self::assertEquals(0, $resultUp->getCommandStatus(), \json_encode($resultUp->getCommandOutput()));

        $this->assertTableExists('pimee_data_quality_insights_criteria_evaluation');
        $this->assertTableExists('pimee_data_quality_insights_product_axis_rates');
        $this->assertTableExists('pimee_data_quality_insights_dashboard_rates_projection');
        $this->assertTableExists('pimee_data_quality_insights_text_checker_dictionary');
    }

    private function assertTableExists(string $tableName)
    {
        $stmt = $this->get('database_connection')->executeQuery(
            'select * from information_schema.tables where table_name=:table_name',
            ['table_name' => $tableName]
        );
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        self::assertIsArray($result);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
