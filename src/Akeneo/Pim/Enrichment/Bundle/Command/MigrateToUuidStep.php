<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

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

    public function getMissingCount(): int;

    public function addMissing(bool $dryRun, OutputInterface $output): void;

    public function shouldBeExecuted(): bool;

    public function getDescription(): string;
}
