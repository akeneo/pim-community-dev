<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Queue;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Doctrine\DBAL\Connection;

/**
 * Repository to persist and get the state of the job execution messages in the queue.
 *
 * As it used by a daemon, it uses directly the DBAL to avoid any memory leak or connection problem due to the UOW.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionMessageRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Gets the job instance code associated to a job execution message.
     */
    public function getJobInstanceCode(JobExecutionMessageInterface $jobExecutionMessage): ?string
    {
        $sql = <<<SQL
SELECT 
    code
FROM
    akeneo_batch_job_execution je 
    JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id
WHERE 
    je.id = :id
LIMIT 1;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $jobExecutionMessage->getJobExecutionId());
        $stmt->execute();
        $data = $stmt->fetch();

        $code = $data['code'] ?? null;

        return $code;
    }
}
