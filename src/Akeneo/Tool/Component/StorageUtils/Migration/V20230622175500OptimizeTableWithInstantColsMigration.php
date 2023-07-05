<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Migration;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20230622175500OptimizeTableWithInstantColsMigration implements ZddMigration
{
    private const TABLES_THAT_CAN_HAVE_INSTANT_COLS = [
        'akeneo_asset_manager_asset',
        'akeneo_asset_manager_asset_family',
        'akeneo_asset_manager_attribute',
        'akeneo_batch_job_execution',
        'akeneo_batch_step_execution',
        'akeneo_connectivity_connection',
        'akeneo_reference_entity_record',
        'akeneo_rule_engine_rule_definition',
        'oro_access_group',
        'oro_user',
        'pim_api_client',
        'pim_catalog_association_type',
        'pim_catalog_category',
        'pim_catalog_category_template',
        'pim_catalog_product_model',
        'pim_data_quality_insights_product_model_score',
        'pimee_data_quality_insights_text_checker_dictionary',
        'pimee_workflow_product_model_draft',
    ];

    public function __construct(private Connection $connection)
    {
    }

    public function migrate(): void
    {
        foreach (self::TABLES_THAT_CAN_HAVE_INSTANT_COLS as $tableName) {
            $this->optimizeTable($tableName);
        }
    }

    public function migrateNotZdd(): void
    {
        $this->migrate();
    }

    public function getName(): string
    {
        return 'OptimizeTableWithInstantCols';
    }

    private function optimizeTable(string $tableName)
    {
        $sql = <<<SQL
            OPTIMIZE TABLE :table_name;
        SQL;

        $this->connection->executeStatement($sql, [
            'table_name' => $tableName
        ]);
    }
}
