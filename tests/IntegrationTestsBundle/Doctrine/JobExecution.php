<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecution
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Allows to know if a specific job instance still has running job
     * executions after a certain time (in seconds). This check is performed
     * every 200 milliseconds.
     *
     * @param string $jobInstanceCode
     * @param int    $time
     *
     * @return bool
     */
    public function isRunning(string $jobInstanceCode, int $time): bool
    {
        $loop = 0;
        $count = 0;
        $maxLoopNumber = $time * 5;

        while ($maxLoopNumber > $loop &&
            0 !== $count = $this->countRunningJobs($jobInstanceCode)
        ) {
            usleep(200000);
            $loop++;
        }

        if (0 !== $count) {
            return true;
        }

        return false;
    }

    /**
     * Finds the number of current execution for an instance job.
     *
     * @param string $jobInstanceCode
     *
     * @return int
     */
    private function countRunningJobs(string $jobInstanceCode): int
    {
        $sql = <<<SQL
SELECT count(`execution`.`id`)
FROM `akeneo_batch_job_execution` AS `execution`
LEFT JOIN `akeneo_batch_job_instance` AS `instance` ON `execution`.`job_instance_id` = `instance`.`id`
WHERE `instance`.`code` = :job_instance_code
AND `execution`.`exit_code` != 'COMPLETED'
SQL;

        return (int) $this->entityManager->getConnection()->fetchColumn($sql, [
            'job_instance_code' => $jobInstanceCode,
        ]);
    }
}
