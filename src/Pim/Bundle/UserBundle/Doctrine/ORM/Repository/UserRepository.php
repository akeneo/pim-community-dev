<?php

namespace Pim\Bundle\UserBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Bundle\UserBundle\Repository\UserRepositoryInterface;

/**
 * User repository
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['username'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.username = :identifier OR u.email = :identifier')
           ->setParameter(':identifier', $identifier);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByGroupIds(array $groupIds)
    {
        if (empty($groupIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('u');
        $qb->leftJoin('u.groups', 'g');
        $qb->where($qb->expr()->in('g.id', $groupIds));

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
