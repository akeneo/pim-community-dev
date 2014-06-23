<?php

namespace PimEnterprise\Bundle\ImportExportBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository as PimJobInstanceRepository;

/**
 * Override job instance repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobInstanceRepository extends PimJobInstanceRepository
{
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
