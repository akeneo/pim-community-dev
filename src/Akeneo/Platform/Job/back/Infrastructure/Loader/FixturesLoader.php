<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Loader;

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

    public function createJobInstance(array $data): int
    {
        $defaultData = [
            'label' => null,
            'status' => 0,
            'connector' => 'Akeneo CSV Connector',
            'raw_parameters' => [],
            'type' => 'export',
        ];

        $this->dbalConnection->insert(
            'akeneo_batch_job_instance',
            array_merge($defaultData, $data),
            [
                'status' => Types::INTEGER,
                'raw_parameters' => Types::ARRAY,
            ]
        );

        return (int)$this->dbalConnection->lastInsertId();
    }

    public function createJobExecution(array $data): int
    {
        $defaultData = [
            'status' => 0,
            'raw_parameters' => [],
        ];

        $this->dbalConnection->insert(
            'akeneo_batch_job_execution',
            array_merge($defaultData, $data),
            [
                'status' => Types::INTEGER,
                'raw_parameters' => Types::JSON,
            ]
        );

        return (int)$this->dbalConnection->lastInsertId();
    }
}
