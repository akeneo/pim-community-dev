<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddUuidColumnsSubscriber implements EventSubscriberInterface
{
    private const TABLES = [
        'pim_catalog_product' => 'uuid',
        'pim_catalog_association' => 'owner_uuid',
        'pim_catalog_association_product' => 'product_uuid',
        'pim_catalog_association_product_model_to_product' => 'product_uuid',
        'pim_catalog_category_product' => 'product_uuid',
        'pim_catalog_group_product' => 'product_uuid',
        'pim_catalog_product_unique_data' => 'product_uuid',
        'pim_catalog_completeness' => 'product_uuid',
        'pim_data_quality_insights_product_criteria_evaluation' => 'product_uuid',
        'pim_data_quality_insights_product_score' => 'product_uuid',
        'pimee_teamwork_assistant_completeness_per_attribute_group' => 'product_uuid',
        'pimee_teamwork_assistant_project_product' => 'product_uuid',
        'pimee_workflow_product_draft' => 'product_uuid',
        'pimee_workflow_published_product' => 'original_product_uuid',
        'pim_versioning_version' => 'resource_uuid',
        'pim_comment_comment' => 'resource_uuid',
    ];

    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['addUuidColumns', -50],
        ];
    }

    public function addUuidColumns(InstallerEvent $event): void
    {
        foreach (self::TABLES as $tableName => $columnName) {
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnName)) {
                $this->addUuidColumn(
                    $tableName,
                    $columnName
                );
            }
        }
    }

    private function addUuidColumn(string $tableName, string $uuidColumName): void
    {
        $addUuidColumnSql = <<<SQL
            ALTER TABLE `{table_name}` ADD `{uuid_column_name}` BINARY(16) DEFAULT NULL;
        SQL;

        $addUuidColumnQuery = \strtr(
            $addUuidColumnSql,
            [
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumName,
            ]
        );

        $this->connection->executeQuery($addUuidColumnQuery);
    }

    private function tableExists(string $tableName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
                SHOW TABLES LIKE :tableName
            SQL,
            ['tableName' => $tableName]
        );

        return count($rows) >= 1;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr(
                <<<SQL
                    SHOW COLUMNS FROM {table_name} LIKE :columnName
                SQL,
                ['{table_name}' => $tableName]
            ),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }
}
