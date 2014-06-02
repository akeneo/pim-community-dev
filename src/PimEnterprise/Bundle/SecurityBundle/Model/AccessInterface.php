<?php

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Oro\Bundle\UserBundle\Entity\Role;

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
