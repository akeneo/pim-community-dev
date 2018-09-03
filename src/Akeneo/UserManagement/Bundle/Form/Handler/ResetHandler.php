<?php

namespace Akeneo\UserManagement\Bundle\Form\Handler;

use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * Overridden ResetHandler in order to manage the reset password
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetHandler
{
    /**
     * {@inheritdoc}
     */
    protected function onSuccess(UserInterface $user)
    {
        $user
            ->setPlainPassword($this->form->getData()->getPlainPassword())
            ->setConfirmationToken(null)
            ->setPasswordRequestedAt(null)
            ->setEnabled(true);

        $this->manager->updateUser($user);
    }
}
