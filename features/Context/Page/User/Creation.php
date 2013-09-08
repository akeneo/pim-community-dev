<?php

namespace Context\Page\User;

use Context\Page\Base\Form;

/**
 * User creation page
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /**
     * @var string $path
     */
    protected $path = '/user/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Statuses' => array('css' => '#oro_user_user_form_enabled'),
                'Owners'   => array('css' => '#oro_user_user_form_owner'),
                'Roles'    => array('css' => '#oro_user_user_form_rolesCollection'),
            )
        );
    }

    /**
     * Select the status
     * @param string $status
     */
    public function selectStatus($status)
    {
        $this->getElement('Statuses')->selectOption($status);
    }

    /**
     * Select the owner
     * @param string $owner
     */
    public function selectOwner($owner)
    {
        $this->getElement('Owners')->selectOption($owner);
    }

    /**
     * Check role
     * @param string $role
     */
    public function selectRole($role)
    {
        $roleLabels = $this->getElement('Roles')->findAll('css', 'label');
        $roleId = null;
        foreach ($roleLabels as $roleLabel) {
            if ($roleLabel->getText() == $role) {
                $roleId = $roleLabel->getAttribute('for');
                break;
            }
        }

        if (!$roleId) {
            throw new \Exception(sprintf('Could not find the role %s', $role));
        }

        $role = $this->getElement('Roles')->find('css', '#'.$roleId);
        $role->check();
    }
}
