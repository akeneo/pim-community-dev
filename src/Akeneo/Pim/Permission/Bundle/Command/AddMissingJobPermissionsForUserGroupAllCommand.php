<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * When jobs are public, the client can configure the permission of the job via the UI.
 * But when a job is internal, they cannot configure anything. The aim of this command is to allow the permissions
 * for internal jobs and user group All.
 */
final class AddMissingJobPermissionsForUserGroupAllCommand extends Command
{
    protected static $defaultName = 'pimee:permission:add-missing-job-permissions-for-user-group';
    private $jobCodes = [
        'update_product_value',
        'add_product_value',
        'remove_product_value',
        'publish_product',
        'unpublish_product',
        'edit_common_attributes',
        'delete_products_and_product_models',
        'add_to_group',
        'add_attribute_value',
        'set_attribute_requirements',
        'approve_product_draft',
        'refuse_product_draft',
        'csv_product_quick_export',
        'csv_product_grid_context_quick_export',
        'csv_published_product_quick_export',
        'csv_published_product_grid_context_quick_export',
        'xlsx_product_quick_export',
        'xlsx_product_grid_context_quick_export',
        'xlsx_published_product_quick_export',
        'xlsx_published_product_grid_context_quick_export',
        'rule_impacted_product_count',
        'project_calculation',
        'refresh_project_completeness_calculation',
        'compute_completeness_of_products_family',
        'compute_family_variant_structure_changes',
        'remove_non_existing_product_values',
        'change_parent_product',
        'asset_manager_compute_transformations',
        'franklin_insights_subscribe_products',
        'franklin_insights_unsubscribe_products',
        'franklin_insights_fetch_products',
        'franklin_insights_remove_attribute_from_mapping',
        'franklin_insights_remove_option_from_mapping',
        'franklin_insights_resubscribe_products',
        'franklin_insights_identify_products_to_resubscribe',
        'franklin_insights_synchronize',
    ];

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Set missing permissions for user group "All" and internal jobs');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->userGroupAllNotExists()) {
            $this->createUserGroupAllIfNeeded();
            $output->writeln('The "All" user group is created');
        } else {
            $output->writeln('The "All" user group already exists');
        }

        $countUsersNotAttachedToUserGroup = $this->countUserNotAttachedToUserGroup();
        if (0 < $countUsersNotAttachedToUserGroup) {
            $this->attachAllExistingUsersToUserGroupAll();
            $output->writeln(sprintf('%d user are added in "All" user group', $countUsersNotAttachedToUserGroup));
        } else {
            $output->writeln('All users are already attached to "All" user group');
        }

        $countMissingPermissions = $this->countMissingPermissions();
        if (0 < $countMissingPermissions) {
            $this->createPermissionsIfNeeded();
            $output->writeln(sprintf('%d permissions are created for "All" user group', $countMissingPermissions));
        } else {
            $output->writeln('All permissions already exist');
        }

        return 0;
    }

    private function userGroupAllNotExists(): bool
    {
        $sql = <<<SQL
          SELECT EXISTS(
              SELECT 1 FROM oro_access_group WHERE name = 'All'
          ) as is_existing
SQL;

        $statement = $this->connection->executeQuery($sql);
        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return !Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }

    private function createUserGroupAllIfNeeded(): void
    {
        $this->connection->executeQuery(<<<SQL
INSERT INTO oro_access_group (name) VALUES ('All') ON DUPLICATE KEY UPDATE id = id;
SQL);
    }

    private function countUserNotAttachedToUserGroup(): int
    {
        $count = $this->connection->executeQuery(<<<SQL
SELECT count(u.id) as count
FROM oro_user u
    CROSS JOIN oro_access_group oag
    LEFT JOIN oro_user_access_group ug ON ug.user_id = u.id AND oag.id = ug.group_id
WHERE oag.name = 'All' AND ug.user_id IS NULL
SQL)->fetchColumn();

        return (int) $count;
    }

    private function attachAllExistingUsersToUserGroupAll(): void
    {
        $this->connection->executeQuery(<<<SQL
INSERT INTO oro_user_access_group (user_id, group_id)
SELECT u.id, oag.id
FROM oro_user u
    CROSS JOIN oro_access_group oag
    LEFT JOIN oro_user_access_group ug ON ug.user_id = u.id AND oag.id = ug.group_id
WHERE oag.name = 'All' AND ug.user_id IS NULL
SQL);
    }

    private function countMissingPermissions(): int
    {
        $sql = <<<SQL
SELECT count(ji.id)
FROM akeneo_batch_job_instance ji
    CROSS JOIN oro_access_group oap
    LEFT JOIN pimee_security_job_profile_access pa On pa.user_group_id = oap.id AND pa.job_profile_id = ji.id
WHERE oap.name = 'All' AND pa.id IS NULL AND ji.code in (:job_codes)
SQL;
        $count = $this->connection->executeQuery(
            $sql,
            ['job_codes' => $this->jobCodes],
            ['job_codes' => Connection::PARAM_STR_ARRAY]
        )->fetchColumn();

        return (int) $count;
    }

    private function createPermissionsIfNeeded(): void
    {
        $sql = <<<SQL
INSERT INTO pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
SELECT ji.id as job_profile_id, oap.id as user_group_id, 1, 0
FROM akeneo_batch_job_instance ji
    CROSS JOIN oro_access_group oap
    LEFT JOIN pimee_security_job_profile_access pa On pa.user_group_id = oap.id AND pa.job_profile_id = ji.id
WHERE oap.name = 'All' AND pa.id IS NULL AND ji.code in (:job_codes)
SQL;

        $this->connection->executeQuery(
            $sql,
            ['job_codes' => $this->jobCodes],
            ['job_codes' => Connection::PARAM_STR_ARRAY]
        );
    }
}
