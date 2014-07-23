<?php

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Oro\Bundle\UserBundle\Entity\Group;

/**
 * Base interface for all access entities
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
