<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Entity;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Contact extends Page implements Entity
{
    protected $first_name;
    protected $last_name;
    protected $street;
    protected $city;
    protected $zip_code;
    protected $email;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function init()
    {
        $this->first_name = $this->byId('orocrm_contact_form_values_1_varchar');
        $this->last_name = $this->byId('orocrm_contact_form_values_2_varchar');
        $this->email = $this->byId('orocrm_contact_form_values_10_varchar');
        $this->street = $this->byId('orocrm_contact_form_multiAddress_0_street');
        $this->city = $this->byId('orocrm_contact_form_multiAddress_0_city');
        $this->zip_code = $this->byId('orocrm_contact_form_multiAddress_0_postalCode');

        return $this;
    }

    public function setFirst_name($first_name)
    {
        $this->first_name->clear();
        $this->first_name->value($first_name);
        return $this;
    }

    public function getFirst_name()
    {
        return $this->first_name->value();
    }

    public function setLast_name($last_name)
    {
        $this->last_name->clear();
        $this->last_name->value($last_name);
        return $this;
    }

    public function getLast_name()
    {
        return $this->last_name->value();
    }

    public function setEmail($email)
    {
        $this->email->clear();
        $this->email->value($email);
        return $this;
    }

    public function getEmail()
    {
        return $this->last_name->value();
    }

    public function setStreet($street)
    {
        $this->street->clear();
        $this->street->value($street);
        return $this;
    }

    public function getStreet()
    {
        return $this->street->value();
    }

    public function setCity($city)
    {
        $this->city->clear();
        $this->city->value($city);
        return $this;
    }

    public function getCity()
    {
        return $this->city->value();
    }

    public function setZip_code($zip_code)
    {
        $this->zip_code->clear();
        $this->zip_code->value($zip_code);
        return $this;
    }

    public function getZip_code()
    {
        return $this->zip_code->value();
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
        return new Contacts($this->test);
    }

    public function edit()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Update contact']")->click();
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
        return new Contacts($this->test, false);
    }
}
