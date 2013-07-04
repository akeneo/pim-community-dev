<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Entity;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Account extends Page implements Entity
{
    protected $account_name;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);

    }

    public function init()
    {
        $this->account_name = $this->byId('orocrm_account_form_name');

        return $this;
    }

    public function setAccount_name($account_name)
    {
        $this->account_name->clear();
        $this->account_name->value($account_name);
        return $this;
    }

    public function getAccount_name()
    {
        return $this->first_name->value();
    }

    public function save()
    {
        $this->byXPath("//button[contains(., 'Save')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function close()
    {
        return new Accounts($this->test);
    }

    public function edit()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit account']")->click();
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
        return new Accounts($this->test, false);
    }
}
