<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * Job execution repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionRepository extends EntityRepository implements DatagridRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->addSelect('e.id')
            ->addSelect('e.status AS status')
            ->addSelect(
                "CONCAT('pim_import_export.batch_status.', e.status) as statusLabel"
            )
            ->addSelect('e.startTime as date')
            ->addSelect('j.code AS jobCode')
            ->addSelect('j.label AS jobLabel')
            ->addSelect('j.jobName as jobName')
            ->addSelect('COUNT(w.id) as warningCount');

        $qb->innerJoin('e.jobInstance', 'j');
        $qb->leftJoin('e.stepExecutions', 's');
        $qb->leftJoin('s.warnings', 'w');
        $qb->andWhere('j.type = :jobType');

        $qb->groupBy('e.id');

        return $qb;
    }
}
