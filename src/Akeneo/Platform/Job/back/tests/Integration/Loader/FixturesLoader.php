<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Loader;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FixturesLoader
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function resetFixtures()
    {
        $resetQuery = <<<SQL
            SET foreign_key_checks = 0;

            DELETE FROM akeneo_batch_job_instance;
            DELETE FROM akeneo_batch_job_execution;
            DELETE FROM akeneo_batch_step_execution;

            SET foreign_key_checks = 1;
SQL;
        $this->dbalConnection->executeQuery($resetQuery);
    }

    public function loadProductImportExportFixtures()
    {
        $jobInstances = [
            'a_product_import' => $this->createJobInstance([
                'code' => 'a_product_import',
                'job_name' => 'a_product_import',
                'label' => 'a_product_import',
                'type' => 'import',
            ]),
            'another_product_import' => $this->createJobInstance([
                'code' => 'another_product_import',
                'job_name' => 'another_product_import',
                'label' => 'another_product_import',
                'type' => 'import',
            ]),
            'a_product_export' => $this->createJobInstance([
                'code' => 'a_product_export',
                'job_name' => 'a_product_export',
                'label' => 'a_product_export',
                'type' => 'export',
            ]),
            'another_product_export' => $this->createJobInstance([
                'code' => 'another_product_export',
                'job_name' => 'another_product_export',
                'label' => 'another_product_export',
                'type' => 'export',
            ]),
            'prepare_evaluation' => $this->createJobInstance([
                'code' => 'prepare_evaluation',
                'job_name' => 'prepare_evaluation',
                'label' => 'prepare_evaluation',
                'type' => 'data_quality_insights',
            ]),
        ];

        $jobExecutions = [
            'a_job_execution' => $this->createJobExecution([
                'job_instance_id' => $jobInstances['a_product_import']
            ])
        ];

        return [
            'job_instances' => $jobInstances,
            'job_executions' => $jobExecutions,
        ];
    }

    public function createJobInstance(array $data): int
    {
        $defaultData = [
            'label' => null,
            'status' => 0,
            'connector' => 'Akeneo CSV Connector',
            'raw_parameters' => [],
            'type' => 'export',
        ];

        $dataToInsert = array_merge($defaultData, $data);
        $dataToInsert['raw_parameters'] = serialize($dataToInsert['raw_parameters']);

        $this->dbalConnection->insert(
            'akeneo_batch_job_instance',
            $dataToInsert
        );

        return (int)$this->dbalConnection->lastInsertId();
    }

    public function createJobExecution(array $data): int
    {
        $defaultData = [
            'status' => 1,
            'raw_parameters' => [],
        ];

        $this->dbalConnection->insert(
            'akeneo_batch_job_execution',
            array_merge($defaultData, $data),
            [
                'raw_parameters' => Types::JSON,
            ]
        );

        return (int)$this->dbalConnection->lastInsertId();
    }

    public function createStepExecution(array $data): int
    {
        $defaultData = [
            'status' => 0,
            'read_count' => 0,
            'write_count' => 0,
            'filter_count' => 0,
            'failure_exceptions' => [],
            'errors' => [],
            'summary' => [],
            'warning_count' => 0,
        ];

        $dataToInsert = array_merge($defaultData, $data);
        $dataToInsert['failure_exceptions'] = serialize($dataToInsert['failure_exceptions']);
        $dataToInsert['errors'] = serialize($dataToInsert['errors']);
        $dataToInsert['summary'] = serialize($dataToInsert['summary']);

        $this->dbalConnection->insert(
            'akeneo_batch_step_execution',
            $dataToInsert
        );

        return (int)$this->dbalConnection->lastInsertId();
    }
}
