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
class Version_2_0_20170925181205_add_default_parameters_in_job_instances extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected const JOB_INSTANCE_CODES = [
        'add_product_value',
        'csv_association_type_export',
        'csv_association_type_import',
        'csv_attribute_export',
        'csv_attribute_group_export',
        'csv_attribute_group_import',
        'csv_attribute_import',
        'csv_attribute_option_export',
        'csv_attribute_option_import',
        'csv_category_export',
        'csv_category_import',
        'csv_channel_export',
        'csv_channel_import',
        'csv_currency_export',
        'csv_currency_import',
        'csv_family_export',
        'csv_family_import',
        'csv_family_variant_export',
        'csv_family_variant_import',
        'csv_group_export',
        'csv_group_import',
        'csv_group_type_export',
        'csv_group_type_import',
        'csv_locale_export',
        'csv_locale_import',
        'csv_product_export',
        'csv_product_grid_context_quick_export',
        'csv_product_import',
        'csv_product_model_export',
        'csv_product_model_import',
        'csv_product_quick_export',
        'edit_common_attributes',
        'remove_product_value',
        'set_attribute_requirements',
        'update_product_value',
        'xlsx_association_type_export',
        'xlsx_association_type_import',
        'xlsx_attribute_export',
        'xlsx_attribute_group_export',
        'xlsx_attribute_group_import',
        'xlsx_attribute_import',
        'xlsx_attribute_option_export',
        'xlsx_attribute_option_import',
        'xlsx_category_export',
        'xlsx_category_import',
        'xlsx_channel_export',
        'xlsx_channel_import',
        'xlsx_currency_export',
        'xlsx_currency_import',
        'xlsx_family_export',
        'xlsx_family_import',
        'xlsx_family_variant_export',
        'xlsx_family_variant_import',
        'xlsx_group_export',
        'xlsx_group_import',
        'xlsx_group_type_export',
        'xlsx_group_type_import',
        'xlsx_locale_export',
        'xlsx_locale_import',
        'xlsx_product_export',
        'xlsx_product_grid_context_quick_export',
        'xlsx_product_import',
        'xlsx_product_model_export',
        'xlsx_product_model_import',
        'xlsx_product_quick_export',
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
