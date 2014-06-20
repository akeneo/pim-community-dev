<?php

namespace PimEnterprise\Bundle\ImportExportBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobExecutionRepository as PimJobExecutionRepository;

/**
 * Override of job execution repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobExecutionRepository extends PimJobExecutionRepository
{
    /**
     * Get last operations
     *
     * @see JobExecutionRepository::getLastOperationsData()
     *
     * @param array        $types
     * @param QueryBuilder $subQB
     *
     * @return array
     */
    public function getLastOperations(array $types, QueryBuilder $subQB)
    {
        $qb = parent::getLastOperationsQB($types);
        $qb
            ->andWhere($qb->expr()->in('j.id', $subQB->getDQL()))
            ->setParameters($subQB->getParameters());

        return $qb->getQuery()->getArrayResult();
    }
}
