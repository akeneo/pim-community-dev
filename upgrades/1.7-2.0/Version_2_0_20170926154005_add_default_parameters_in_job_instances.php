<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add keys "user_to_notify" and "is_user_authenticated" in the configuration of the job instances.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_2_0_20170926154005_add_default_parameters_in_job_instances extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected const JOB_INSTANCE_CODES = [
        'add_tags_to_assets',
        'apply_assets_mass_upload',
        'approve_product_draft',
        'classify_assets',
        'csv_asset_category_export',
        'csv_asset_category_import',
        'csv_asset_export',
        'csv_asset_import',
        'csv_asset_variation_export',
        'csv_option_export',
        'csv_option_import',
        'csv_product_import_with_rules',
        'csv_product_proposal_import',
        'csv_published_product_export',
        'csv_published_product_grid_context_quick_export',
        'csv_published_product_quick_export',
        'project_calculation',
        'publish_product',
        'refresh_project_completeness_calculation',
        'refuse_product_draft',
        'rule_impacted_product_count',
        'unpublish_product',
        'xlsx_asset_category_export',
        'xlsx_asset_category_import',
        'xlsx_asset_export',
        'xlsx_asset_import',
        'xlsx_asset_variation_export',
        'xlsx_option_export',
        'xlsx_option_import',
        'xlsx_product_import_with_rules',
        'xlsx_product_proposal_import',
        'xlsx_published_product_export',
        'xlsx_published_product_grid_context_quick_export',
        'xlsx_published_product_quick_export',
        'yml_asset_channel_configuration_export',
        'yml_asset_channel_configuration_import',
        'yml_rule_export',
        'yml_rule_import',
    ];
    
    /**
     * @param Schema $schema
     *
     * @throws \Exception
     */
    public function up(Schema $schema)
    {
        $schemaHelper = new SchemaHelper($this->container);
        $jobInstanceTable = $schemaHelper->getTableOrCollection('job_instance');

        $stmt = $this->connection->executeQuery(
            sprintf('SELECT code, raw_parameters FROM %s WHERE code IN (?)', $jobInstanceTable),
            [self::JOB_INSTANCE_CODES],
            [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        );

        $jobInstances = $stmt->fetchAll();

        if (null === $jobInstances) {
            throw new \Exception('No job instance has been found.');
        }

        $migratedJobInstances = [];
        foreach ($jobInstances as $jobInstance) {
            $parameters = unserialize($jobInstance['raw_parameters']);
            $parameters['user_to_notify'] = null;
            $parameters['is_user_authenticated'] = false;
            $jobInstance['raw_parameters'] = serialize($parameters);

            $migratedJobInstances[] = $jobInstance;
        }

        $this->connection->beginTransaction();

        try {
            foreach ($migratedJobInstances as $migratedJobInstance) {
                $this->connection->update(
                    'akeneo_batch_job_instance',
                    ['raw_parameters' => $migratedJobInstance['raw_parameters']],
                    ['code'           => $migratedJobInstance['code']]
                );
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
