<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\User\Model\GroupInterface;

class GroupRepository extends EntityRepository
{
    /**
     * Get user query builder
     *
     * @param  GroupInterface $group
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(GroupInterface $group)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('PimUserBundle:User', 'u')
            ->join('u.groups', 'groups')
            ->where('groups = :group')
            ->setParameter('group', $group);
    }
}
