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
use Pim\Bundle\UserBundle\Entity\User;

/**
 * Category Access Repository
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class CategoryAccessRepository extends EntityRepository
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
     * Count the accesses of the categories for user groups other than 'ALL'
     */
    public function countCustomAccesses()
    {
        $qb = $this->createQueryBuilder('a');

        return (int) $qb
            ->select('COUNT(DISTINCT a.category)')
            ->innerJoin('OroUserBundle:Group', 'g', 'WITH', 'a.userGroup = g.id')
            ->where('g.name <> :default_group')
            ->setParameter('default_group', User::GROUP_DEFAULT)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
