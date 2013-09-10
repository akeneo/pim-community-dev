<?php

namespace Oro\Bundle\TagBundle\Entity;

use Oro\Bundle\UserBundle\Entity\User;

interface ContainUpdaterInterface
{
    /**
     * Return related user
     *
     * @return User
     */
    public function getUpdatedBy();

    /**
     * Set updater user
     *
     * @param User $user
     * @return mixed
     */
    public function setUpdatedBy(User $user);
}
