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
     * Get data for the last operations widget
     *
     * @param array $types Job types to show
     *
     * @return array
     */
    public function getLastOperationsData(array $types)
    {
        $qb = $this->getLastOperationsQB($types);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get last operations query builder
     *
     * @param array $types
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getLastOperationsQB(array $types)
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->select('e.id, e.startTime as date, j.type, j.label, e.status')
            ->innerJoin('e.jobInstance', 'j')
            ->orderBy('e.startTime', 'DESC')
            ->setMaxResults(10);

        if (!empty($types)) {
            $qb->andWhere($qb->expr()->in('j.type', $types));
        }

        return $qb;
    }

    /**
     * Create datagrid query builder
     *
     * @return \Doctrine\ORM\QueryBuilder
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
            ->addSelect('j.alias as jobAlias')
        ;

        $qb->innerJoin('e.jobInstance', 'j');

        $qb->andWhere('j.type = :jobType');

        return $qb;
    }

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
            ->addSelect(
                "CONCAT('pim_import_export.batch_status.', e.status) as statusLabel"
            )
            ->addSelect('e.startTime as startTime')
            ->addSelect('j.label AS jobLabel')
            ->addSelect('e.user AS user')
        ;

        $qb->innerJoin('e.jobInstance', 'j');

        return $qb;
    }
}
