<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Loader;

use Doctrine\DBAL\Connection;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FixturesLoader
{
    private array $jobInstances = [];

    public function __construct(
        private Connection $dbalConnection,
        private FixturesJobHelper $fixturesJobHelper,
        private FixturesUserHelper $fixturesUserHelper
    ) {
    }

    public function resetFixtures(): void
    {
        $resetQuery = <<<SQL
            SET foreign_key_checks = 0;

            DELETE FROM akeneo_batch_job_instance;
            DELETE FROM akeneo_batch_job_execution;
            DELETE FROM akeneo_batch_step_execution;
            DELETE FROM oro_user;
            DELETE FROM oro_user_access_role;
            DELETE FROM oro_access_role;

            SET foreign_key_checks = 1;
SQL;
        $this->dbalConnection->executeQuery($resetQuery);
    }

    public function loadFixtures(): void
    {
        $this->loadUsers();
        $this->loadJobInstances();
        $this->loadJobExecutions();
    }

    private function loadUsers(): void
    {
        $this->fixturesUserHelper->createRole('ROLE_NO_ACL', []);
        $this->fixturesUserHelper->createRole('ROLE_ADMINISTRATOR', [
            'pim_enrich_job_tracker_view_all_jobs',
            'pim_enrich_job_tracker_index',
            'pim_importexport_stop_job'
        ]);

        $this->fixturesUserHelper->createRole('ROLE_IMPORT_EXPORT_VIEWER', [
            'pim_importexport_import_execution_show'
        ]);

        $this->fixturesUserHelper->createRole('PROCESS_TRACKER_VIEWER', [
            'pim_enrich_job_tracker_index',
        ]);

        $this->fixturesUserHelper->createUser('peter', ['ROLE_ADMINISTRATOR']);
        $this->fixturesUserHelper->createUser('mary', ['ROLE_IMPORT_EXPORT_VIEWER', 'PROCESS_TRACKER_VIEWER']);
        $this->fixturesUserHelper->createUser('betty', ['ROLE_NO_ACL']);
    }

    private function loadJobInstances(): void
    {
        $this->jobInstances = [
            'a_product_import' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'a_product_import',
                'job_name' => 'a_product_import',
                'label' => 'a_product_import',
                'type' => 'import',
            ]),
            'another_product_import' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'another_product_import',
                'job_name' => 'another_product_import',
                'label' => 'another_product_import',
                'type' => 'import',
            ]),
            'a_product_export' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'a_product_export',
                'job_name' => 'a_product_export',
                'label' => 'a_product_export',
                'type' => 'export',
            ]),
            'another_product_export' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'another_product_export',
                'job_name' => 'another_product_export',
                'label' => 'another_product_export',
                'type' => 'export',
            ]),
            'a_not_visible_instance' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'prepare_evaluation',
                'job_name' => 'prepare_evaluation',
                'label' => 'prepare_evaluation',
                'type' => 'data_quality_insights',
            ]),
        ];
    }

    private function loadJobExecutions(): void
    {
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'peter',
            'job_instance_id' => $this->jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'mary',
            'job_instance_id' => $this->jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'peter',
            'job_instance_id' => $this->jobInstances['a_product_export'],
        ]);
    }
}
