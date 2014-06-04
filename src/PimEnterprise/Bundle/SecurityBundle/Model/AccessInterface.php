<?php

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Oro\Bundle\UserBundle\Entity\Role;

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
     * Get role
     *
     * @return Role
     */
    public function getRole();

    /**
     * Set role
     *
     * @param Role $role
     *
     * @return AccessInterface
     */
    public function setRole(Role $role);
}
