<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * Job execution repository.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionRepository extends EntityRepository implements DatagridRepositoryInterface
{
    public const HEALTH_CHECK_INTERVAL = 10;

    public function __construct(EntityManager $em, string $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->addSelect('e.id')
            ->addSelect('j.type AS type')
            ->addSelect('e.status AS status')
            ->addSelect(
                "CONCAT('pim_import_export.batch_status.', e.status) as statusLabel"
            )
            ->addSelect('e.startTime as date')
            ->addSelect('j.code AS jobCode')
            ->addSelect('j.label AS jobLabel')
            ->addSelect('j.jobName as jobName')
            ->addSelect('SUM(s.warningCount) as warningCount');

        $qb->innerJoin('e.jobInstance', 'j');
        $qb->leftJoin('e.stepExecutions', 's');
        $qb->andWhere('j.type = :jobType');
        $qb->andWhere('e.isVisible = 1');

        $qb->groupBy('e.id');

        return $qb;
    }

    public function isOtherJobExecutionRunning(JobExecution $jobExecution): bool
    {
        $sql = <<< SQL
        SELECT EXISTS(
            SELECT 1 FROM akeneo_batch_job_execution
            WHERE job_instance_id = :job_instance_id 
              AND STATUS IN (2, 3, 4) 
              AND id <> :job_execution_id
              AND health_check_time > :health_check_time_limit
        )
        SQL;

        $healthCheckLimit = new \DateTime('now', new \DateTimeZone('UTC'));
        $healthCheckLimit->modify(sprintf('-%d seconds', self::HEALTH_CHECK_INTERVAL));

        return (bool) $this->getEntityManager()->getConnection()->executeQuery(
            $sql,
            [
                'job_instance_id' => $jobExecution->getJobInstance()->getId(),
                'job_execution_id' => $jobExecution->getId(),
                'health_check_time_limit' => $healthCheckLimit->format('Y-m-d H:i:s'),
            ]
        )->fetchOne();
    }
}
