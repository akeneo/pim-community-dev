<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * Job instance repository.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceRepository extends EntityRepository implements DatagridRepositoryInterface
{
    private JobRegistry $jobRegistry;

    /**
     * @param string $class
     */
    public function __construct(JobRegistry $jobRegistry, EntityManager $em, $class)
    {
        $this->jobRegistry = $jobRegistry;
        parent::__construct($em, $em->getClassMetadata($class));
    }

    public function createDatagridQueryBuilder()
    {
        $jobName = array_map(
            fn (JobInterface $job) => $job->getName(),
            $this->jobRegistry->all()
        );

        $qb = $this->createQueryBuilder('j');
        $qb
            ->addSelect('j.jobName as jobName')
            ->addSelect(
                "CONCAT('pim_import_export.status.', j.status) as statusLabel"
            )
            ->andWhere('j.type = :jobType')
            ->andWhere(
                $qb->expr()->in('j.jobName', $jobName)
            );

        return $qb;
    }
}
