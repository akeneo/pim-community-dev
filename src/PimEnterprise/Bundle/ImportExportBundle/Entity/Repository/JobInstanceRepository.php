<?php

namespace PimEnterprise\Bundle\ImportExportBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;

use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository as PimJobInstanceRepository;

class JobInstanceRepository extends PimJobInstanceRepository
{
    public function addGridAccessQB(QueryBuilder $qb, QueryBuilder $subQB)
    {
        $qb
            ->andWhere(
                $qb->expr()->in('j.id', $subQB->getDQL())
            );
    }
}
