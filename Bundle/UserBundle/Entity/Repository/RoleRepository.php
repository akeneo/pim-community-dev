<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\UserBundle\Entity\Role;

class RoleRepository extends EntityRepository
{
    /**
     * Get user query builder
     *
     * @param  Role         $role
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(Role $role)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('OroUserBundle:User', 'u')
            ->join('u.roles', 'role')
            ->where('role = :role')
            ->setParameter('role', $role);
    }
}
