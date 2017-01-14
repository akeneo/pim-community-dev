<?php

namespace Pim\Bundle\ImportExportBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Job execution repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\ImportExportBundle\Doctrine\ORM\Repository in 1.4
 */
class JobExecutionRepository extends EntityRepository
{
    /**
     * Create job tracker datagrid query builder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createJobTrackerDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->addSelect('e.id')
            ->addSelect('j.type AS type')
            ->addSelect('e.status AS status')
            ->addSelect(
                "CONCAT('pim_import_export.batch_status.', e.status) as statusLabel"
            )
            ->addSelect('e.startTime as startTime')
            ->addSelect('j.label AS jobLabel')
            ->addSelect('e.user AS user')
            ->innerJoin('e.jobInstance', 'j');

        return $qb;
    }
}
