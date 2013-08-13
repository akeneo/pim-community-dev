<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class BusinessUnits extends PageFilteredGrid
{
    const URL = 'organization/business_unit';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    public function add()
    {
        $this->test->byXPath("//div[@class = 'container-fluid']//a[contains(., 'Create business unit')]")->click();
        //due to bug BAP-965
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        $businessunit = new BusinessUnit($this->test);
        return $businessunit->init();
    }

    /**
     * @param array $entityData
     * @return BusinessUnit
     */
    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new BusinessUnit($this->test);
    }

    /**
     * @param $unitname
     * @param $contextname
     * @return $this
     */
    public function checkContextMenu($unitname, $contextname)
    {
        $this->filterBy('Name', $unitname);
        $this->waitForAjax();
        if ($this->isElementPresent("//td[@class='action-cell']//a[contains(., '...')]")) {
            $this->byXPath("//td[@class='action-cell']//a[contains(., '...')]")->click();
            $this->waitForAjax();
            return $this->assertElementNotPresent("//td[@class='action-cell']//a[@title= '{$contextname}']");
        }

        return $this;
    }
}
