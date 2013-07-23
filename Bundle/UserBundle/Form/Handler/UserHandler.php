<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\TagBundle\Form\Handler\TagHandlerInterface;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;


class UserHandler extends AbstractUserHandler implements TagHandlerInterface
{
    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * @var BusinessUnitManager
     */
    protected $businessUnitManager;

    /**
     * {@inheritdoc}
     */
    public function process(User $user)
    {
        $this->form->setData($user);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $businessUnits = $this->request->get('businessUnits');
                if ($businessUnits) {
                    $this->businessUnitManager->assignBusinessUnits($user, array_keys($businessUnits));
                }
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(User $user)
    {
        $this->manager->updateUser($user);
        $this->tagManager->saveTagging($user);

        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        $this->manager->reloadUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function setTagManager(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * @param BusinessUnitManager $businessUnitManager
     */
    public function setBusinessUnitManager(BusinessUnitManager $businessUnitManager)
    {
        $this->businessUnitManager = $businessUnitManager;
    }
}
