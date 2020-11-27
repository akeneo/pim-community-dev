<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql;

use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlCreateJobInstance implements CreateJobInstanceInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function createJobInstance(array $params): int
    {
        $sql = 'INSERT IGNORE INTO akeneo_batch_job_instance 
            (code, label, job_name, status, connector, raw_parameters, type)
        VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type);';

        $defaultParams =[
            'status' => 0,
            'connector' => 'internal',
            'raw_parameters' => 'a:0:{}',
        ];

        return $this->connection->executeUpdate($sql, array_merge($defaultParams, $params));
    }
}
