<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\UserBundle\Entity\User;

class UserHandler extends AbstractUserHandler
{
    protected function onSuccess(User $user)
    {
        $this->manager->updateUser($user);

        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        $this->manager->reloadUser($user);
    }
}
