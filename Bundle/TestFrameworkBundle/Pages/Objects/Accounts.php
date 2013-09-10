<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class Accounts extends PageFilteredGrid
{
    const URL = 'account';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    public function add()
    {
        $this->test->byXPath("//a[@title='Create account']")->click();
        //due to bug BAP-965
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        $account = new Account($this->test);
        return $account->init();
    }

    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Account($this->test);
    }
}
