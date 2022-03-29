<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MigrateToUuidStep
{
    public const TABLES = [
        'pim_catalog_product' => [
            'id',
            'uuid',
            null,
            null,
            [],
            [],
        ],
        'pim_catalog_association' => [
            'owner_id',
            'owner_uuid',
            null,
            'FK_CC27100147D93336',
            ['owner_uuid_association_type_id_idx' => ['owner_uuid', 'association_type_id']],
            [],
            [],
        ],
        'pim_catalog_association_product' => [
            'product_id',
            'product_uuid',
            ['association_id', 'product_uuid'],
            'FK_3A3A49D45C977207',
            [],
            [],
        ],
        'pim_catalog_association_product_model_to_product' => [
            'product_id',
            'product_uuid',
            ['association_id', 'product_uuid'],
            'FK_3FF3ED195C977207',
            [],
            [],
        ],
        'pim_catalog_category_product' => [
            'product_id',
            'product_uuid',
            ['product_uuid', 'category_id'],
            'pim_catalog_category_product_todo_rename_with_doctrine_name',
            [],
            [],
        ],
        'pim_catalog_group_product' => [
            'product_id',
            'product_uuid',
            ['product_uuid', 'group_id'],
            'pim_catalog_group_product_todo_rename_with_doctrine_name2',
            [],
            [],
        ],
        'pim_catalog_product_unique_data' => [
            'product_id',
            'product_uuid',
            null,
            null,
            [],
            [],
        ],
        'pim_data_quality_insights_product_criteria_evaluation' => [
            'product_id',
            'product_uuid',
            ['product_uuid', 'criterion_code'],
            'FK_dqi_product_uuid_criteria_evaluation',
            [],
            [],
        ],
        'pim_data_quality_insights_product_score' => [
            'product_id',
            'product_uuid',
            ['product_uuid', 'evaluated_at'],
            'FK_dqi_product_uuid_score',
            [],
            [],
        ],
        'pimee_teamwork_assistant_completeness_per_attribute_group' => [
            'product_id',
            'product_uuid',
            ['locale_id','channel_id','product_uuid','attribute_group_id'],
            'attr_grp_completeness_product_uuid_foreign_key',
            [],
            [],
        ],
        'pimee_teamwork_assistant_project_product' => [
            'product_id',
            'product_uuid',
            ['project_id','product_uuid'],
            'product_selection_project_uuid_foreign_key',
            [],
            [],
        ],
        'pimee_workflow_product_draft' => [
            'product_id',
            'product_uuid',
            null,
            'pimee_workflow_product_draft_todo_rename_with_doctrine_name',
            ['author_product_uuid_idx' => ['author', 'product_uuid']],
            [],
        ],
        'pimee_workflow_published_product' => [
            'original_product_id',
            'original_product_uuid',
            null,
            'pimee_workflow_published_product_todo_rename_with_doctrine_name',
            [],
            [],
        ],
        'pim_versioning_version' => [
            'resource_id',
            'resource_uuid',
            null,
            null,
            [],
            ['resource_name_resource_uuid_version_idx' => ['resource_name','resource_uuid','version']],
        ],
    ];
    public const ID_COLUMN_INDEX = 0;
    public const UUID_COLUMN_INDEX = 1;
    public const PRIMARY_KEY_UUID_INDEX = 2;
    public const FOREIGN_KEY_INDEX = 3;
    public const CONSTRAINTS_INDEX = 4;
    public const INDEXES_INDEX = 5;

    /**
     * Returns the exact item count to be migrated.
     * The computation of this count can be expensive, to use with caution.
     */
    public function getMissingCount(): int;

    public function addMissing(Context $context): bool;

    /**
     * Returns if the migration has to be executed or not
     */
    public function shouldBeExecuted(): bool;

    public function getDescription(): string;

    public function getName(): string;

    public function getStatus(): string;

    public function getDuration(): ?float;

    public function setStatusInProgress(): void;

    public function setStatusInError(): void;

    public function setStatusDone(): void;

    public function setStatusSkipped(): void;
}
