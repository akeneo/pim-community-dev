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
     * @var string
     */
    protected $path = '#/user/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Roles' => ['css' => '#pim_user_user_form_rolesCollection'],
            )
        );
    }

    /**
     * Check role
     *
     * @param string $role
     */
    public function selectRole($role)
    {
        $roleLabels = $this->getElement('Roles')->findAll('css', 'label');

        foreach ($roleLabels as $roleLabel) {
            if ($roleLabel->getText() == $role) {
                $roleLabel->click();

                return;
            }
        }

        throw new \Exception(sprintf('Could not find the role %s', $role));
    }
}
