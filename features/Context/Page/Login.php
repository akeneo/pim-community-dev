<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Login extends Page
{
    protected $path = '/{locale}/user/login';

    protected $elements = array(
        'Login form' => array('css' => '.form-signin')
    );

    public function login($username, $password)
    {
        $element = $this->getElement('Login form');
        $element->fillField('_username', $username);
        $element->fillField('_password', $password);
        $element->pressButton('Log in');
    }
}

