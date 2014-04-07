<?php

namespace Pim\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\Handler\AbstractUserHandler;
use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;

/**
 * Overriden UserHandler to remove tag management
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserHandler extends AbstractUserHandler
{
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
                $businessUnits = $this->request->get('businessUnits', array());
                if ($businessUnits) {
                    $businessUnits = array_keys($businessUnits);
                }
                if ($this->businessUnitManager) {
                    $this->businessUnitManager->assignBusinessUnits($user, $businessUnits);
                }
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user
     */
    protected function onSuccess(User $user)
    {
        $this->manager->updateUser($user);
        // Reloads the user to reset its username. This is needed when the
        // username or password have been changed to avoid issues with the
        // security layer.
        $this->manager->reloadUser($user);
    }

    /**
     * @param BusinessUnitManager $businessUnitManager
     */
    public function setBusinessUnitManager(BusinessUnitManager $businessUnitManager)
    {
        $this->businessUnitManager = $businessUnitManager;
    }
}
