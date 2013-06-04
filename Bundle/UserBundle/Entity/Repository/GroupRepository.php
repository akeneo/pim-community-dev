<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\UserBundle\Entity\Group;

class GroupRepository extends EntityRepository
{
    /**
     * Get user query builder
     *
     * @param  Group        $group
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(Group $group)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('OroUserBundle:User', 'u')
            ->join('u.groups', 'groups')
            ->where('groups = :group')
            ->setParameter('group', $group);
    }
}
