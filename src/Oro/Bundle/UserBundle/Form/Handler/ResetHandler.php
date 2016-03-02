<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Pim\Bundle\UserBundle\Entity\UserInterface;

class ResetHandler extends AbstractUserHandler
{
    /**
     * {@inheritDoc}
     */
    protected function onSuccess(UserInterface $user)
    {
        $user
            ->setPlainPassword($this->form->getData()->plainPassword)
            ->setConfirmationToken(null)
            ->setPasswordRequestedAt(null)
            ->setEnabled(true);

        $this->manager->updateUser($user);
    }
}
