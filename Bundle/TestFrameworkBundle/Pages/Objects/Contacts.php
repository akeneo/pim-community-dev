<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class Contacts extends PageFilteredGrid
{
    const URL = 'contact';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    public function add()
    {
        $this->test->byXPath("//a[@title='Create contact')]")->click();
        //due to bug BAP-965
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        $contact = new Contact($this->test);
        return $contact->init();
    }

    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Contact($this->test);
    }
}
