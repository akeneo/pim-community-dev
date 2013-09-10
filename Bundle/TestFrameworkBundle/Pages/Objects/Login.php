<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Login extends Page
{
    public function __construct($testCase, $args = array('url' => '/'))
    {
        if (array_key_exists('url', $args)) {
            $this->redirectUrl = $args['url'];
        }
        parent::__construct($testCase);

        if (array_key_exists('remember', $args)) {
            $this->byId('remember_me')->click();
        }

        $this->username = $this->byId('prependedInput');
        $this->password = $this->byId('prependedInput2');
    }

    public function setUsername($value)
    {
        $this->username->clear();
        $this->username->value($value);
        return $this;
    }

    public function setPassword($value)
    {
        $this->password->clear();
        $this->password->value($value);
        return $this;
    }

    public function submit()
    {
        $this->byId('_submit')->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function loggedIn()
    {
        if (strtolower($this->title()) == 'login' or $this->url()=='user/login') {
            return false;
        } else {
            return true;
        }
    }
}
