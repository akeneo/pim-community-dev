<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Pim\Bundle\UserBundle\Entity\UserInterface;

class UserHandler extends AbstractUserHandler
{
    /**
     * {@inheritdoc}
     */
    public function process(UserInterface $user)
    {
        $this->form->setData($user);

        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($this->getRequest());

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(UserInterface $user)
    {
        $this->manager->updateUser($user);

        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        $this->manager->reloadUser($user);
    }
}
