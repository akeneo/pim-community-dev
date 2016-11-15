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

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\Group;

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
    public function findContributorToNotify(ProjectInterface $project)
    {
        $qb = $this->createQueryBuilder('u');

        $groupIds = array_map(function (Group $userGroup) {
            return $userGroup->getId();
        }, $project->getUserGroups()->toArray());

        if (empty($groupIds)) {
            return [];
        }

        $qb->leftJoin('u.groups', 'g')
            ->where($qb->expr()->neq('u.id', $project->getOwner()->getId()))
            ->andWhere($qb->expr()->in('g.id', $groupIds));

        return $qb->getQuery()->getResult();
    }
}
