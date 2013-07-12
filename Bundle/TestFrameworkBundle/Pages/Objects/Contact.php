<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Entity;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Contact extends Page implements Entity
{
    protected $firstname;
    protected $lastname;
    protected $email;
    protected $primary;
    protected $street;
    protected $city;
    protected $zipcode;
    protected $country;
    protected $state;
    protected $assignedto;
    protected $reportsto;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function init()
    {
        $this->firstname = $this->byId('orocrm_contact_form_values_1_varchar');
        $this->lastname = $this->byId('orocrm_contact_form_values_2_varchar');
        $this->email = $this->byId('orocrm_contact_form_values_10_varchar');
        $this->primary = $this->byId('orocrm_contact_form_addresses_0_primary');
        $this->street = $this->byId('orocrm_contact_form_addresses_0_street');
        $this->city = $this->byId('orocrm_contact_form_addresses_0_city');
        $this->zipcode = $this->byId('orocrm_contact_form_addresses_0_postalCode');
        $this->country = $this->byXpath("//div[@id='s2id_orocrm_contact_form_addresses_0_country']/a");
        $this->state = $this->byXpath("//div[@id='s2id_orocrm_contact_form_addresses_0_state']/a");
        $this->assignedto = $this->byXpath("//div[@id='s2id_orocrm_contact_form_values_8_user']/a");
        $this->reportsto = $this->byXpath("//div[@id='s2id_orocrm_contact_form_values_9_contact']/a");

        return $this;
    }

    public function setFirstName($firstname)
    {
        $this->firstname->clear();
        $this->firstname->value($firstname);
        return $this;
    }

    public function getFirstName()
    {
        return $this->firstname->value();
    }

    public function setLastName($lastname)
    {
        $this->lastname->clear();
        $this->lastname->value($lastname);
        return $this;
    }

    public function getLastName()
    {
        return $this->lastname->value();
    }

    public function setEmail($email)
    {
        $this->email->clear();
        $this->email->value($email);
        return $this;
    }

    public function getEmail()
    {
        return $this->email->value();
    }

    public function setPrimary()
    {
        $this->primary->click();

        return $this;
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

    public function setZipCode($zipcode)
    {
        $this->zipcode->clear();
        $this->zipcode->value($zipcode);
        return $this;
    }

    public function getZipCode()
    {
        return $this->zipcode->value();
    }

    public function setCountry($country)
    {
        $this->country->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($country);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$country}')]", "Country's autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$country}')]")->click();

        return $this;
    }

    public function setState($state)
    {
        $this->state->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($state);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$state}')]", "Country's autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$state}')]")->click();

        return $this;
    }

    public function setAssignedTo($assignedto)
    {
        $this->assignedto->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($assignedto);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$assignedto}')]", "Assigned to autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$assignedto}')]")->click();

        return $this;
    }

    public function setReportsTo($reportsto)
    {
        $this->reportsto->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($reportsto);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$reportsto}')]", "Reports to autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$reportsto}')]")->click();

        return $this;
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
