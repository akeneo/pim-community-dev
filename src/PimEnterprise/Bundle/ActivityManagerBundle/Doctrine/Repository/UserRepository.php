<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class UserRepository extends EntityRepository implements UserRepositoryInterface
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
     * {@inheritdoc}
     */
    public function findByGroupIdsProjectOwnerExcluded($projectOwnerId, array $groupIds)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->leftJoin('u.groups', 'g');
        $qb->where('u.id != :ownerId');
        $qb->andWhere($qb->expr()->in('g.id', $groupIds));
        $qb->setParameter('ownerId', $projectOwnerId);

        return $qb->getQuery()->getResult();
    }
}
