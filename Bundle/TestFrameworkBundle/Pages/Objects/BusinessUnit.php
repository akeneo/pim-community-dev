<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class BusinessUnit extends AbstractEntity implements Entity
{
    protected $businessunitname;

    public function init()
    {
        $this->businessunitname = $this->byId('oro_business_unit_form_name');
        $this->owner = $this->select($this->byId('oro_business_unit_form_owner'));

        return $this;
    }

    /**
     * @param $unitname
     * @return $this
     */
    public function setBusinessUnitName($unitname)
    {
        $this->businessunitname->clear();
        $this->businessunitname->value($unitname);
        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessUnitName()
    {
        return $this->businessunitname->value();
    }

    public function setOwner($owner)
    {
        $this->owner->selectOptionByLabel($owner);

        return $this;
    }

    public function getOwner()
    {
        return trim($this->owner->selectedLabel());
    }

    public function edit()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit business unit']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->init();
        return $this;
    }

    public function delete()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[contains(., 'Delete')]")->click();
        $this->byXPath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new BusinessUnits($this->test, false);
    }
}
