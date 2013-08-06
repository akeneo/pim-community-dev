<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class BusinessUnit extends AbstractEntity implements Entity
{
    protected $businessunitname;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function init()
    {
        $this->businessunitname = $this->byId('oro_business_unit_form_name');

        return $this;
    }

    public function setBusinessUnitName($accountname)
    {
        $this->businessunitname->clear();
        $this->businessunitname->value($accountname);
        return $this;
    }

    public function getBusinessUnitName()
    {
        return $this->businessunitname->value();
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
