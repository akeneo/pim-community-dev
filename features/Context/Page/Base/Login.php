<?php

namespace Context\Page\Base;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Login page
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Login extends Page
{
    protected $path = '#/user/login';

    protected $elements = array(
        'Login form' => array('css' => '.form-signin')
    );

    /**
     * Log in the user
     *
     * @param string $username
     * @param string $password
     */
    public function authenticate($username, $password)
    {
        $element = $this->getElement('Login form');
        $element->fillField('_username', $username);
        $element->fillField('_password', $password);
        $element->pressButton('Log in');
    }
}
