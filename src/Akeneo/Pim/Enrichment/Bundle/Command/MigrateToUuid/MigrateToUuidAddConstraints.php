<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidAddConstraints implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    private array $constraintsToAdd = [
        [
            'tableName' => 'pim_catalog_association',
            'constraintName' => 'owner_uuid_association_type_id_idx',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_association
                    ADD CONSTRAINT {constraintName} UNIQUE (`owner_uuid`, `association_type_id`),
                    ALGORITHM=INPLACE,
                    LOCK=NONE
            SQL
        ], [
            'tableName' => 'pim_catalog_association',
            'constraintName' => 'owner_uuid_association_type_id_idx',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_association
                    ADD CONSTRAINT `FK_CC27100147D93336` FOREIGN KEY (`owner_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL
        ], [
            'tableName' => 'pim_catalog_association_product',
            'constraintName' => 'migrate_to_uuid_temp_index_to_delete',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_association_product 
                    ADD CONSTRAINT {constraintName} UNIQUE (`association_id`, `product_id`),
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`association_id`, `product_uuid`), 
                    ALGORITHM=INPLACE,
                    LOCK=NONE;
            SQL,
        ], [
            'tableName' => 'pim_catalog_association_product',
            'constraintName' => 'FK_3A3A49D45C977207',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_association_product
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL,
        ], [
            'tableName' => 'pim_catalog_association_product_model_to_product',
            'constraintName' => 'migrate_to_uuid_temp_index_to_delete',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_association_product_model_to_product 
                    ADD CONSTRAINT {constraintName} UNIQUE (`association_id`, `product_id`),
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`association_id`, `product_uuid`),
                    ALGORITHM=INPLACE,
                    LOCK=NONE
            SQL,
        ], [
            'tableName' => 'pim_catalog_association_product_model_to_product',
            'constraintName' => 'FK_3FF3ED195C977207',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_association_product_model_to_product 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL,
        ], [
            'tableName' => 'pim_catalog_category_product',
            'constraintName' => 'migrate_to_uuid_temp_index_to_delete',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_category_product 
                    ADD CONSTRAINT {constraintName} UNIQUE (`product_id`, `category_id`),
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`product_uuid`, `category_id`), 
                    ALGORITHM=INPLACE, 
                    LOCK=NONE
            SQL,
        ], [
            'tableName' => 'pim_catalog_category_product',
            'constraintName' => 'pim_catalog_category_product_todo_rename_with_doctrine_name',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_category_product 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL,
        ], [
            'tableName' => 'pim_catalog_group_product',
            'constraintName' => 'migrate_to_uuid_temp_index_to_delete',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_group_product 
                    ADD CONSTRAINT {constraintName} UNIQUE (`product_id`, `group_id`),
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`product_uuid`, `group_id`),
                    ALGORITHM=INPLACE, 
                    LOCK=NONE
            SQL,
        ], [
            'tableName' => 'pim_catalog_group_product',
            'constraintName' => 'pim_catalog_group_product_todo_rename_with_doctrine_name2',
            'query' => <<<SQL
                ALTER TABLE pim_catalog_group_product 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL,
        ], [
            'tableName' => 'pim_data_quality_insights_product_criteria_evaluation',
            'constraintName' => 'migrate_to_uuid_temp_index_to_delete',
            'query' => <<<SQL
                ALTER TABLE pim_data_quality_insights_product_criteria_evaluation 
                    ADD CONSTRAINT {constraintName} UNIQUE (`product_id`, `criterion_code`),
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`product_uuid`, `criterion_code`), 
                    ALGORITHM=INPLACE,
                    LOCK=NONE
            SQL
        ], [
            'tableName' => 'pim_data_quality_insights_product_criteria_evaluation',
            'constraintName' => 'FK_dqi_product_uuid_criteria_evaluation',
            'query' => <<<SQL
                ALTER TABLE pim_data_quality_insights_product_criteria_evaluation 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL
        ], [
            'tableName' => 'pim_data_quality_insights_product_score',
            'constraintName' => 'migrate_to_uuid_temp_index_to_delete',
            'query' => <<<SQL
                ALTER TABLE pim_data_quality_insights_product_score 
                    ADD CONSTRAINT {constraintName} UNIQUE (`product_id`, `evaluated_at`),
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`product_uuid`, `evaluated_at`), 
                    ALGORITHM=INPLACE,
                    LOCK=NONE
            SQL,
        ], [
            'tableName' => 'pim_data_quality_insights_product_score',
            'constraintName' => 'FK_dqi_product_uuid_score',
            'query' => <<<SQL
                ALTER TABLE pim_data_quality_insights_product_score 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL,
        ], [
            'tableName' => 'pimee_teamwork_assistant_completeness_per_attribute_group',
            'constraintName' => 'migrate_to_uuid_temp_index_to_delete',
            'query' => <<<SQL
                ALTER TABLE pimee_teamwork_assistant_completeness_per_attribute_group 
                    ADD CONSTRAINT {constraintName} UNIQUE (`locale_id`,`channel_id`,`product_id`,`attribute_group_id`),
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`locale_id`,`channel_id`,`product_uuid`,`attribute_group_id`), 
                    ALGORITHM=INPLACE,
                    LOCK=NONE
            SQL,
        ], [
            'tableName' => 'pimee_teamwork_assistant_completeness_per_attribute_group',
            'constraintName' => 'attr_grp_completeness_product_uuid_foreign_key',
            'query' => <<<SQL
                ALTER TABLE pimee_teamwork_assistant_completeness_per_attribute_group 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL,
        ], [
            'tableName' => 'pimee_teamwork_assistant_project_product',
            'constraintName' => 'migrate_to_uuid_temp_index_to_delete',
            'query' => <<<SQL
                ALTER TABLE pimee_teamwork_assistant_project_product 
                    ADD CONSTRAINT {constraintName} UNIQUE (`project_id`,`product_id`),
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (`project_id`,`product_uuid`), 
                    ALGORITHM=INPLACE,
                    LOCK=NONE
            SQL,
        ], [
            'tableName' => 'pimee_teamwork_assistant_project_product',
            'constraintName' => 'product_selection_project_uuid_foreign_key',
            'query' => <<<SQL
                ALTER TABLE pimee_teamwork_assistant_project_product 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL,
        ], [
            'tableName' => 'pimee_workflow_product_draft',
            'constraintName' => 'author_product_uuid_idx',
            'query' => <<<SQL
                ALTER TABLE pimee_workflow_product_draft 
                    ADD CONSTRAINT {constraintName} UNIQUE (`author`, `product_uuid`),
                    ALGORITHM=INPLACE,
                    LOCK=NONE
            SQL,
        ], [
            'tableName' => 'pimee_workflow_product_draft',
            'constraintName' => 'pimee_workflow_product_draft_todo_rename_with_doctrine_name',
            'query' => <<<SQL
                ALTER TABLE pimee_workflow_product_draft 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            SQL,
        ], [
            'tableName' => 'pimee_workflow_published_product',
            'constraintName' => 'pimee_workflow_published_product_todo_rename_with_doctrine_name',
            'query' => <<<SQL
                ALTER TABLE pimee_workflow_published_product 
                    ADD CONSTRAINT {constraintName} FOREIGN KEY (`original_product_uuid`) REFERENCES `pim_catalog_product` (`uuid`)
            SQL,
        ],
    ];

    private array $indexesToAdd = [
        [
            'tableName' => 'pim_versioning_version',
            'indexName' => 'resource_name_resource_uuid_version_idx',
            'query' => <<<SQL
                    ALTER TABLE pim_versioning_version 
                        ADD INDEX {indexName} (`resource_name`,`resource_uuid`,`version`),
                        ALGORITHM=INPLACE,
                        LOCK=NONE
                SQL,
        ]
    ];

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function getDescription(): string
    {
        return 'Add constraints on uuid foreign columns';
    }

    public function getName(): string
    {
        return 'add_constraints_on_uuid_columns';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach ($this->constraintsToAdd as $constraint) {
            if ($this->tableExists($constraint['tableName']) && !$this->constraintExists($constraint['tableName'], $constraint['constraintName'])) {
                $count++;
            }
        }

        foreach ($this->indexesToAdd as $indexToAdd) {
            if ($this->tableExists($indexToAdd['tableName']) && !$this->indexExists($indexToAdd['tableName'], $indexToAdd['indexName'])) {
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        $updatedItems = 0;

        foreach ($this->constraintsToAdd as $constraint) {
            $logContext->addContext('substep', $constraint['tableName']);

            if ($this->tableExists($constraint['tableName']) && !$this->constraintExists($constraint['tableName'], $constraint['constraintName'])) {
                $this->logger->notice(sprintf('Will add %s constraint', $constraint['constraintName']), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->connection->executeQuery(\strtr($constraint['query'], ['{constraintName}' => $constraint['constraintName']]));
                    $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                }
            }
        }

        foreach ($this->indexesToAdd as $indexToAdd) {
            $logContext->addContext('substep', $indexToAdd['tableName']);

            if ($this->tableExists($indexToAdd['tableName']) && !$this->indexExists($indexToAdd['tableName'], $indexToAdd['indexName'])) {
                $this->logger->notice(sprintf('Will add %s index', $indexToAdd['indexName']), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->connection->executeQuery(\strtr($indexToAdd['query'], ['{indexName}' => $indexToAdd['indexName']]));
                    $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                }
            }
        }

        return true;
    }
}
