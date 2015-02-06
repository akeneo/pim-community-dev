<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Interface for access repository
 *
 * @author Romain Monceau <romain@akeneo.com>
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
