<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Entity;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Contact extends Page implements Entity
{
    protected $nameprefix;
    protected $firstname;
    protected $lastname;
    protected $namesuffix;
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
        $this->nameprefix = $this->byId('orocrm_contact_form_values_name_prefix_varchar');
        $this->firstname = $this->byId('orocrm_contact_form_values_first_name_varchar');
        $this->lastname = $this->byId('orocrm_contact_form_values_last_name_varchar');
        $this->namesuffix = $this->byId('orocrm_contact_form_values_name_suffix_varchar');
        $this->email = $this->byId('orocrm_contact_form_values_email_varchar');
        $this->assignedto = $this->byXpath("//div[@id='s2id_orocrm_contact_form_values_assigned_to_user']/a");
        $this->reportsto = $this->byXpath("//div[@id='s2id_orocrm_contact_form_values_reports_to_contact']/a");
        $this->tags = $this->byXpath("//div[@id='s2id_orocrm_contact_form_tags']");

        $this->primary = $this->byId('orocrm_contact_form_addresses_0_primary');
        $this->street = $this->byId('orocrm_contact_form_addresses_0_street');
        $this->city = $this->byId('orocrm_contact_form_addresses_0_city');
        $this->zipcode = $this->byId('orocrm_contact_form_addresses_0_postalCode');
        $this->country = $this->byXpath("//div[@id='s2id_orocrm_contact_form_addresses_0_country']/a");
        if ($this->byId('orocrm_contact_form_addresses_0_state_text')->displayed()) {
            $this->state = $this->byId('orocrm_contact_form_addresses_0_state_text');
        } else {
            $this->state = $this->byXpath("//div[@id='s2id_orocrm_contact_form_addresses_0_state']/a");
        }

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
        $this->waitForAjax();

        return $this;
    }

    public function setState($state)
    {
        if ($this->byId('orocrm_contact_form_addresses_0_state_text')->displayed()) {
            $this->state = $this->byId('orocrm_contact_form_addresses_0_state_text');
            $this->state->clear();
            $this->state->value($state);
        } else {
            $this->state = $this->byXpath("//div[@id='s2id_orocrm_contact_form_addresses_0_state']/a");
            $this->state->click();
            $this->waitForAjax();
            $this->byXpath("//div[@id='select2-drop']/div/input")->value($state);
            $this->waitForAjax();
            $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$state}')]", "Country's autocoplete doesn't return search value");
            $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$state}')]")->click();
        }

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
