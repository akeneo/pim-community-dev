<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Interface for access repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
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
