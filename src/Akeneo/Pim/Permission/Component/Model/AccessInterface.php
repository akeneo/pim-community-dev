<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Model;

use Akeneo\UserManagement\Component\Model\GroupInterface;

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
     * @return GroupInterface
     */
    public function getUserGroup();

    /**
     * Set user group
     *
     * @param GroupInterface $group
     *
     * @return AccessInterface
     */
    public function setUserGroup(GroupInterface $group);
}
