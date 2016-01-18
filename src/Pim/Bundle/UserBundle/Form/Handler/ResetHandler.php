<?php

namespace Pim\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\UserBundle\Form\Handler\ResetHandler as OroResetHandler;
use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * Overridden ResetHandler in order to manage the reset password
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetHandler extends OroResetHandler
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
