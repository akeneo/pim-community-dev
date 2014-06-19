<?php

namespace Pim\Bundle\ImportExportBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Job execution repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
}
