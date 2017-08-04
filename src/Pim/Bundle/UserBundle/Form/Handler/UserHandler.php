<?php

namespace Pim\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\UserBundle\Form\Handler\AbstractUserHandler;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * Overridden UserHandler to remove tag management
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserHandler extends AbstractUserHandler
{
    /**
     * {@inheritdoc}
     */
    public function process(UserInterface $user)
    {
        $this->form->setData($user);

        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {
            $this->form->handleRequest($this->getRequest());

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    /**
     * @param UserInterface $user
     */
    protected function onSuccess(UserInterface $user)
    {
        $this->addDefaultGroup($user);
        $this->manager->updateUser($user);
        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        $this->manager->reloadUser($user);
    }

    /**
     * Add the default group to the user.
     *
     * @param UserInterface $user
     *
     * @throws \RuntimeException
     */
    protected function addDefaultGroup(UserInterface $user)
    {
        if (!$user->hasGroup(User::GROUP_DEFAULT)) {
            $group = $this->manager->getStorageManager()
                ->getRepository('OroUserBundle:Group')->getDefaultUserGroup();

            if (!$group) {
                throw new \RuntimeException('Default user group not found');
            }

            $user->addGroup($group);
        }
    }
}
