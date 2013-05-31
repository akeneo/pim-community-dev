<?php

namespace Oro\Bundle\UserBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Oro\Bundle\UserBundle\Entity\Role;

class RoleManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get user query builder
     *
     * @param  Role         $role
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(Role $role)
    {
        return $this->getRoleRepo()->getUserQueryBuilder($role);
    }

    /**
     * @return RoleRepository
     */
    protected function getRoleRepo()
    {
        return $this->em->getRepository('OroUserBundle:Role');
    }
}
