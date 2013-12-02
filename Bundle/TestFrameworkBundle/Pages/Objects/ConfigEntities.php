<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class ConfigEntities extends PageFilteredGrid
{
    const URL = 'entity/config/';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    public function add()
    {
        $this->test->byXPath("//a[@title='Create Entity']")->click();
        //due to bug BAP-965
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        $entity = new ConfigEntity($this->test);
        return $entity->init(true);
    }

    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new ConfigEntity($this->test);
    }

    public function delete()
    {
        $this->byXPath("//td[@class='action-cell']//a[contains(., '...')]")->click();
        $this->waitForAjax();
        $this->byXpath("//td[@class='action-cell']//a[@title= 'Remove']")->click();
        //$this->waitForAjax();

        return $this;
    }
}
