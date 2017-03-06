<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Project Repository
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProjectRepository extends EntityRepository
{
    /**
     * @param EntityManager $em
     * @param ClassMetadata $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * Count the total number of projects
     *
     * @return integer
     */
    public function countAll()
    {
        $qb = $this->createQueryBuilder('p');

        return (int) $qb
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
