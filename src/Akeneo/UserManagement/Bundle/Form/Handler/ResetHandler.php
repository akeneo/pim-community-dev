<?php

namespace Akeneo\UserManagement\Bundle\Form\Handler;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Overridden ResetHandler in order to manage the reset password
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetHandler
{
    /** @var FormInterface */
    private $form;

    /** @var UserManager */
    private $manager;

    /**
     * @param FormInterface $form
     * @param UserManager   $manager
     */
    public function __construct(FormInterface $form, UserManager $manager)
    {
        $this->form = $form;
        $this->manager = $manager;
    }

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
