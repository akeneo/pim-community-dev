<?php

namespace PimEnterprise\Bundle\ImportExportBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobExecutionRepository as BaseJobExecutionRepository;

/**
 * Override of job execution repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobExecutionRepository extends BaseJobExecutionRepository
{
    /**
     * Get last operations
     *
     * @param array        $types
     * @param QueryBuilder $subQB
     *
     * @return array
     *
     * @see JobExecutionRepository::getLastOperationsData()
     */
    public function getLastOperations(array $types, QueryBuilder $subQB)
    {
        $qb = parent::getLastOperationsQB($types);
        $qb
            ->andWhere($qb->expr()->in('j.id', $subQB->getDQL()))
            ->setParameters($subQB->getParameters());

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Inject subquery to filter jobs depending on permissions
     *
     * @param QueryBuilder $qb
     * @param QueryBuilder $subQB
     */
    public function addGridAccessQB(QueryBuilder $qb, QueryBuilder $subQB)
    {
        $qb
            ->andWhere(
                $qb->expr()->in('j.id', $subQB->getDQL())
            );
    }
}
