<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class Lead extends AbstractEntity implements Entity
{
    protected $name;
    protected $firstname;
    protected $lastname;
    protected $contact;
    protected $jobtitle;
    protected $phone;
    protected $email;
    protected $companyname;
    protected $website;
    protected $employees;
    protected $industry;
    protected $address;
    protected $owner;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function init()
    {
        $this->name = $this->byId('orocrm_sales_lead_form_name');
        $this->firstname = $this->byId('orocrm_sales_lead_form_firstName');
        $this->lastname = $this->byId('orocrm_sales_lead_form_lastName');
        $this->contact = $this->byXpath("//div[@id='s2id_orocrm_sales_lead_form_contact']/a");
        $this->jobtitle = $this->byId('orocrm_sales_lead_form_jobTitle');
        $this->phone = $this->byId('orocrm_sales_lead_form_phoneNumber');
        $this->email = $this->byId('orocrm_sales_lead_form_email');
        $this->companyname = $this->byId('orocrm_sales_lead_form_companyName');
        $this->website = $this->byId('orocrm_sales_lead_form_website');
        $this->employees = $this->byId('orocrm_sales_lead_form_numberOfEmployees');
        $this->industry = $this->byId('orocrm_sales_lead_form_industry');
        $this->owner = $this->byXpath("//div[@id='s2id_orocrm_sales_lead_form_owner']/a");

        return $this;
    }

    public function setName($name)
    {
        $this->name->clear();
        $this->name->value($name);
        return $this;
    }

    public function getName()
    {
        return $this->name->value();
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

    public function setContact($contact)
    {
        $this->contact->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($contact);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$contact}')]", "Assigned to autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$contact}')]")->click();
    }

    public function getContact()
    {
        return $this->byXpath("//div[@id='s2id_orocrm_sales_lead_form_contact']/a/span")->text();
    }

    public function setJobTitle($jobtitle)
    {
        $this->jobtitle->clear();
        $this->jobtitle->value($jobtitle);
        return $this;
    }

    public function getJobTitle()
    {
        return $this->jobtitle->value();
    }

    public function setPhone($phone)
    {
        $this->phone->clear();
        $this->phone->value($phone);
        return $this;
    }

    public function getPhone()
    {
        return $this->phone->value();
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

    public function setCompany($companyname)
    {
        $this->companyname->clear();
        $this->companyname->value($companyname);
        return $this;
    }

    public function getCompany()
    {
        return $this->companyname->value();
    }

    public function setWebsite($website)
    {
        $this->website->clear();
        $this->website->value($website);
        return $this;
    }

    public function getWebsite()
    {
        return $this->website->value();
    }

    public function setEmployees($employees)
    {
        $this->employees->clear();
        $this->employees->value($employees);
        return $this;
    }

    public function getEmployees()
    {
        return $this->employees->value();
    }

    public function setOwner($owner)
    {
        $this->owner->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($owner);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$owner}')]",
            "Owner autocoplete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$owner}')]")->click();

        return $this;

    }

    public function getOwner()
    {
        return;
    }

    public function setAddressLabel($value)
    {
        $addresslabel = $this->byId("orocrm_sales_lead_form_address_label");
        $addresslabel->clear();
        $addresslabel->value($value);

        return $this;
    }

    public function getAddressLabel()
    {
        $addresslabel = $this->byId("orocrm_sales_lead_form_address_label");
        return $addresslabel->attribute('value');
    }

    public function setAddressStreet($value)
    {
        $addressstreet = $this->byId("orocrm_sales_lead_form_address_street");
        $addressstreet->clear();
        $addressstreet->value($value);

        return $this;
    }

    public function getAddressStreet()
    {
        $addressstreet = $this->byId("orocrm_sales_lead_form_address_street");
        return $addressstreet->attribute('value');
    }

    public function setAddressCity($value)
    {
        $addresscity = $this->byId("orocrm_sales_lead_form_address_city");
        $addresscity->clear();
        $addresscity->value($value);

        return $this;
    }

    public function getAddressCity()
    {
        $addresscity = $this->byId("orocrm_sales_lead_form_address_city");
        return $addresscity->attribute('value');
    }

    public function setAddressZipCode($value)
    {
        $addresscity = $this->byId("orocrm_sales_lead_form_address_postalCode");
        $addresscity->clear();
        $addresscity->value($value);

        return $this;
    }

    public function getAddressZipCode()
    {
        $addresscity = $this->byId("orocrm_sales_lead_form_address_postalCode");
        return $addresscity->attribute('value');
    }

    public function setAddressCountry($value)
    {
        $country = $this->byXpath("//div[@id='s2id_orocrm_sales_lead_form_address_country']/a");
        $country->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($value);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$value}')]", "Country's autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$value}')]")->click();
        $this->waitForAjax();

        return $this;
    }

    public function getAddressCountry()
    {
        return $this->byXpath("//div[@id = 's2id_orocrm_sales_lead_form_address_country']/a/span")->text();
    }

    public function setAddressState($value)
    {
        if ($this->byId("orocrm_sales_lead_form_address_state_text")->displayed()) {
            $this->state = $this->byId("orocrm_sales_lead_form_address_state_text");
            $this->state->clear();
            $this->state->value($value);
        } else {
            $this->state = $this->byXpath("//div[@id='s2id_orocrm_sales_lead_form_address_state']/a");
            $this->state->click();
            $this->waitForAjax();
            $this->byXpath("//div[@id='select2-drop']/div/input")->value($value);
            $this->waitForAjax();
            $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$value}')]", "Country's autocoplete doesn't return search value");
            $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$value}')]")->click();
        }

        return $this;
    }

    public function getAddressState()
    {
        return $this->byXpath("//div[@id = 's2id_orocrm_sales_lead_form_address_state']/a/span")->text();
    }

    public function setAddress($data)
    {
        foreach ($data as $key => $value) {
            $method = 'setAddress' . ucfirst($key);
            $this->$method($value);
        }

        return $this;
    }

    public function getAddress(&$values)
    {
        $values['label'] = $this->getAddressLabel();
        $values['street'] = $this->getAddressStreet();
        $values['city'] = $this->getAddressCity();
        $values['zipCode'] = $this->getAddressZipCode();
        $values['country'] = $this->getAddressCountry();
        $values['state'] = $this->getAddressState();

        return $this;
    }

    public function edit()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Update lead']")->click();
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
