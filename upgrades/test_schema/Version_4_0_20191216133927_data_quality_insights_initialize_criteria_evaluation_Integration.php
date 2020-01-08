<?php
declare(strict_types=1);

namespace Pimee\Upgrade\Schema\Tests;


use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class Version_4_0_20191216133927_data_quality_insights_initialize_criteria_evaluation_Integration extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_does_not_schedule_criteria_evaluation_if_their_is_no_products()
    {
        $this->addEvaluationJob();

        $resultUp = $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel())
        );
        self::assertEquals(0, $resultUp->getCommandStatus(), \json_encode($resultUp->getCommandOutput()));

        $stmt = $this->get('database_connection')->executeQuery(
            'SELECT je.raw_parameters FROM akeneo_batch_job_execution as je LEFT JOIN akeneo_batch_job_instance as ji ON (ji.id=je.job_instance_id) WHERE ji.job_name=:code',
            ['code' => EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME]
        );
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        self::assertEquals(null, $result);
    }

    public function test_it_schedule_criteria_evaluation_if_products_are_present()
    {
        $this->insertProducts();
        $this->addEvaluationJob();

        $resultUp = $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel())
        );
        self::assertEquals(0, $resultUp->getCommandStatus(), \json_encode($resultUp->getCommandOutput()));

        $stmt = $this->get('database_connection')->executeQuery(
            'SELECT je.raw_parameters FROM akeneo_batch_job_execution as je LEFT JOIN akeneo_batch_job_instance as ji ON (ji.id=je.job_instance_id) WHERE ji.job_name=:code',
            ['code' => EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME]
        );
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        self::assertIsArray($result);
        self::assertCount(7, json_decode($result['raw_parameters'], true)['product_ids']);
    }

    private function addEvaluationJob()
    {
        $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', '_4_0_20191210100000_data_quality_insights_add_evaluation_job')
        );
    }

    private function insertProducts()
    {
        $this->get('database_connection')->executeQuery('DELETE FROM pim_catalog_product');
        $sql = <<<SQL
INSERT INTO pim_catalog_product VALUES
    (NULL, NULL, NULL, NULL, 1, 'product1', '{"name": {"<all_channels>": {"<all_locales>": ""}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product2', '{"name": {"<all_channels>": {"<all_locales>": []}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product3', '{"name": {"<all_channels>": {"<all_locales>": [""]}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product4', '{"name": {"<all_channels>": {"<all_locales>": null}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product5', '{"name": {"<all_channels>": {"<all_locales>": ""}}, "foo": {"<all_channels>": {"<all_locales>": "bar"}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product6', '{"name": {"<all_channels>": {"fr_FR": "", "en_US": "bar"}}}', NOW(), NOW()),
    (NULL, NULL, NULL, NULL, 1, 'product7', '{"name": {"ecommerce": {"<all_locales>": ""}, "mobile": {"<all_locales>": "bar"}}}', NOW(), NOW())
SQL;
        $this->get('database_connection')->executeQuery($sql);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
