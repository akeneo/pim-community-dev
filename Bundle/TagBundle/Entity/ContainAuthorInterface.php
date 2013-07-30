<?php

namespace Oro\Bundle\TagBundle\Entity;

use Oro\Bundle\UserBundle\Entity\User;

interface ContainAuthorInterface
{
    /**
     * Returns user
     *
     * @return User
     */
    public function getCreatedBy();

    /**
     * Set created user
     *
     * @param User $user
     * @return mixed
     */
    public function setCreatedBy(User $user);
}
