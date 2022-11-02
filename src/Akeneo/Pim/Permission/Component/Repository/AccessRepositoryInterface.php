<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Repository;

use Akeneo\UserManagement\Component\Model\UserInterface;

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
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrantedEntitiesQB(UserInterface $user, $accessLevel);
}
