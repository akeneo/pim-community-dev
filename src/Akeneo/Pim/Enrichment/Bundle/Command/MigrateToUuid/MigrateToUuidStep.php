<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MigrateToUuidStep
{
    public const TABLES = [
        'pim_catalog_product' => ['id', 'uuid'],
        'pim_catalog_association' => ['owner_id', 'owner_uuid'],
        'pim_catalog_association_product' => ['product_id', 'product_uuid'],
        'pim_catalog_association_product_model_to_product' => ['product_id', 'product_uuid'],
        'pim_catalog_category_product' => ['product_id', 'product_uuid'],
        'pim_catalog_group_product' => ['product_id', 'product_uuid'],
        'pim_catalog_product_unique_data' => ['product_id', 'product_uuid'],
        'pim_data_quality_insights_product_criteria_evaluation' => ['product_id', 'product_uuid'],
        'pim_data_quality_insights_product_score' => ['product_id', 'product_uuid'],
        'pimee_teamwork_assistant_completeness_per_attribute_group' => ['product_id', 'product_uuid'],
        'pimee_teamwork_assistant_project_product' => ['product_id', 'product_uuid'],
        'pimee_workflow_product_draft' => ['product_id', 'product_uuid'],
        'pimee_workflow_published_product' => ['original_product_id', 'original_product_uuid'],
        'pim_versioning_version' => ['resource_id', 'resource_uuid'],
    ];
    public const ID_COLUMN_INDEX = 0;
    public const UUID_COLUMN_INDEX = 1;

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
