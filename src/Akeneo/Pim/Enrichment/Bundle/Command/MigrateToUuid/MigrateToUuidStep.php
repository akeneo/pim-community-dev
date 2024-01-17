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
            self::ID_COLUMN_INDEX => 'id',
            self::UUID_COLUMN_INDEX => 'uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'product_uuid',
            self::PRIMARY_KEY_UUID_INDEX => ['uuid'],
            self::FOREIGN_KEY_INDEX => null,
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_catalog_association' => [
            self::ID_COLUMN_INDEX => 'owner_id',
            self::UUID_COLUMN_INDEX => 'owner_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'IDX_CC27100147D93336',
            self::PRIMARY_KEY_UUID_INDEX => null,
            self::FOREIGN_KEY_INDEX => 'FK_CC27100147D93336',
            self::UNIQUE_CONSTRAINTS_INDEX => ['owner_uuid_association_type_id_idx' => ['owner_uuid', 'association_type_id']],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_catalog_association_product' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'IDX_3A3A49D45C977207',
            self::PRIMARY_KEY_UUID_INDEX => ['association_id', 'product_uuid'],
            self::FOREIGN_KEY_INDEX => 'FK_3A3A49D45C977207',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_catalog_association_product_model_to_product' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'IDX_3FF3ED195C977207',
            self::PRIMARY_KEY_UUID_INDEX => ['association_id', 'product_uuid'],
            self::FOREIGN_KEY_INDEX => 'FK_3FF3ED195C977207',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_catalog_category_product' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'IDX_512179C15C977207',
            self::PRIMARY_KEY_UUID_INDEX => ['product_uuid', 'category_id'],
            self::FOREIGN_KEY_INDEX => 'FK_512179C15C977207',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => ['IDX_512179C15C977207' => ['product_uuid']],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_catalog_group_product' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'IDX_7AC0C83A5C977207',
            self::PRIMARY_KEY_UUID_INDEX => ['product_uuid', 'group_id'],
            self::FOREIGN_KEY_INDEX => 'FK_7AC0C83A5C977207',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => ['IDX_7AC0C83A5C977207' => ['product_uuid']],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_catalog_product_unique_data' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'IDX_E0768BA35C977207',
            self::PRIMARY_KEY_UUID_INDEX => null,
            self::FOREIGN_KEY_INDEX => 'FK_E0768BA35C977207',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => ['IDX_E0768BA35C977207' => ['product_uuid']],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_catalog_completeness' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'product_uuid',
            self::PRIMARY_KEY_UUID_INDEX => null,
            self::FOREIGN_KEY_INDEX => null,
            self::UNIQUE_CONSTRAINTS_INDEX => ['channel_locale_product_unique_idx' => ['channel_id', 'locale_id', 'product_uuid']],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_data_quality_insights_product_criteria_evaluation' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => null,
            self::PRIMARY_KEY_UUID_INDEX => ['product_uuid', 'criterion_code'],
            self::FOREIGN_KEY_INDEX => 'FK_dqi_product_uuid_criteria_evaluation',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => ['migrate_to_uuid_temporary_index' => ['product_uuid']],
        ],
        'pim_data_quality_insights_product_score' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => null,
            self::PRIMARY_KEY_UUID_INDEX => ['product_uuid', 'evaluated_at'],
            self::FOREIGN_KEY_INDEX => 'FK_dqi_product_uuid_score',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => ['migrate_to_uuid_temporary_index' => ['product_uuid']],
        ],
        'pimee_teamwork_assistant_completeness_per_attribute_group' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'product_uuid',
            self::PRIMARY_KEY_UUID_INDEX => ['locale_id','channel_id','product_uuid','attribute_group_id'],
            self::FOREIGN_KEY_INDEX => 'attr_grp_completeness_product_uuid_foreign_key',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pimee_teamwork_assistant_project_product' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'product_uuid',
            self::PRIMARY_KEY_UUID_INDEX => ['project_id','product_uuid'],
            self::FOREIGN_KEY_INDEX => 'product_selection_project_uuid_foreign_key',
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pimee_workflow_product_draft' => [
            self::ID_COLUMN_INDEX => 'product_id',
            self::UUID_COLUMN_INDEX => 'product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'IDX_6F63A25F5C977207',
            self::PRIMARY_KEY_UUID_INDEX => null,
            self::FOREIGN_KEY_INDEX => 'FK_6F63A25F5C977207',
            self::UNIQUE_CONSTRAINTS_INDEX => ['unique_author_product_idx' => ['author', 'product_uuid']],
            self::INDEXES_INDEX => ['IDX_6F63A25F5C977207' => ['product_uuid']],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pimee_workflow_published_product' => [
            self::ID_COLUMN_INDEX => 'original_product_id',
            self::UUID_COLUMN_INDEX => 'original_product_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => 'UNIQ_E3566E6987DBB431',
            self::PRIMARY_KEY_UUID_INDEX => null,
            self::FOREIGN_KEY_INDEX => 'FK_E3566E6987DBB431',
            self::UNIQUE_CONSTRAINTS_INDEX => ['UNIQ_E3566E6987DBB431' => ['original_product_uuid']],
            self::INDEXES_INDEX => [],
            self::TEMPORARY_INDEXES_INDEX => [],
        ],
        'pim_versioning_version' => [
            self::ID_COLUMN_INDEX => 'resource_id',
            self::UUID_COLUMN_INDEX => 'resource_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => null,
            self::PRIMARY_KEY_UUID_INDEX => null,
            self::FOREIGN_KEY_INDEX => null,
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => ['resource_name_resource_uuid_version_idx' => ['resource_name','resource_uuid','version']],
            self::TEMPORARY_INDEXES_INDEX => ['migrate_to_uuid_temporary_index' => ['resource_name', 'resource_uuid', 'resource_id']],
        ],
        'pim_comment_comment' => [
            self::ID_COLUMN_INDEX => 'resource_id',
            self::UUID_COLUMN_INDEX => 'resource_uuid',
            self::UUID_COLUMN_INDEX_NAME_INDEX => null,
            self::PRIMARY_KEY_UUID_INDEX => null,
            self::FOREIGN_KEY_INDEX => null,
            self::UNIQUE_CONSTRAINTS_INDEX => [],
            self::INDEXES_INDEX => ['resource_name_resource_uuid_idx' => ['resource_name','resource_uuid']],
            self::TEMPORARY_INDEXES_INDEX => ['migrate_to_uuid_temporary_index' => ['resource_name', 'resource_uuid', 'resource_id']],
        ],
    ];
    public const ID_COLUMN_INDEX = 0;
    public const UUID_COLUMN_INDEX = 1;
    public const PRIMARY_KEY_UUID_INDEX = 2;
    public const FOREIGN_KEY_INDEX = 3;
    public const UNIQUE_CONSTRAINTS_INDEX = 4;
    public const INDEXES_INDEX = 5;
    public const UUID_COLUMN_INDEX_NAME_INDEX = 6;
    public const TEMPORARY_INDEXES_INDEX = 7;

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
}
