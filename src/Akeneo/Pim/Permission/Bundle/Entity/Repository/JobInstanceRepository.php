<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Entity\Repository;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobInstanceRepository as BaseJobInstanceRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Override job instance repository
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobInstanceRepository extends BaseJobInstanceRepository
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
