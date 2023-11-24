<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211124154203_add_is_visible_in_job_execution extends AbstractMigration
{
    const NON_VISIBLE_JOBS = [
        'compute_completeness_of_products_family',
        'compute_family_variant_structure_changes',
        'data_quality_insights_evaluations',
        'data_quality_insights_periodic_tasks',
        'data_quality_insights_prepare_evaluations',
        'data_quality_insights_recompute_products_scores',
        'project_calculation',
        'refresh_project_completeness_calculation',
        'remove_completeness_for_channel_and_locale',
        'remove_non_existing_product_values',
    ];

    public function up(Schema $schema): void
    {
        if ($schema->getTable('akeneo_batch_job_execution')->hasColumn('is_visible')) {
            $this->write('is_visible column already exists in akeneo_batch_job_execution');

            return;
        }

        $this->addSql("ALTER TABLE akeneo_batch_job_execution ADD is_visible TINYINT(1) DEFAULT 1");
        $this->addSql("UPDATE akeneo_batch_job_execution SET is_visible = 0 WHERE job_instance_id IN (SELECT id FROM akeneo_batch_job_instance WHERE code IN ('"
            . implode("', '", self::NON_VISIBLE_JOBS) . "'))");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
