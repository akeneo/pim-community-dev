<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Oro\Bundle\UserBundle\Entity\User;

interface AccessRepositoryInterface
{
    /**
     * Get granted entities query builder
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrantedEntitiesQB(User $user, $accessLevel);
}
