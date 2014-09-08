<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Oro\Bundle\UserBundle\Entity\Group;

/**
 * Base interface for all access entities
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface AccessInterface
{
    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get user group
     *
     * @return Group
     */
    public function getUserGroup();

    /**
     * Set user group
     *
     * @param Group $group
     *
     * @return AccessInterface
     */
    public function setUserGroup(Group $group);
}
