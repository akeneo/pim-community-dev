<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class Opportunity extends AbstractEntity implements Entity
{
    protected $name;
    protected $contact;
    protected $account;
    protected $probability;
    protected $budget;
    protected $customerneed;
    protected $proposedsolution;
    protected $closereason;
    protected $closerevenu;
    protected $closedate;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function init()
    {
        $this->name = $this->byId('orocrm_sales_opportunity_form_name');
        $this->contact = $this->byXpath("//div[@id='s2id_orocrm_sales_opportunity_form_contact']/a");
        $this->account = $this->byXpath("//div[@id='s2id_orocrm_sales_opportunity_form_account']/a");
        $this->probability = $this->byId('orocrm_sales_opportunity_form_probability');
        $this->budget = $this->byId('orocrm_sales_opportunity_form_budgetAmount');
        $this->customerneed = $this->byId('orocrm_sales_opportunity_form_customerNeed');
        $this->proposedsolution = $this->byId('orocrm_sales_opportunity_form_proposedSolution');
        $this->closereason = $this->select($this->byId('orocrm_sales_opportunity_form_closeReason'));
        $this->closerevenu = $this->byId('orocrm_sales_opportunity_form_closeRevenue');
        $this->closedate = $this->byId('orocrm_sales_opportunity_form_closeDate');

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

    public function setContact($contact)
    {
        $this->contact->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($contact);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$contact}')]", "Contact autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$contact}')]")->click();
    }

    public function getContact()
    {
        return $this->byXpath("//div[@id='s2id_orocrm_sales_opportunity_form_contact']/a/span")->text();
    }

    public function setAccount($account)
    {
        $this->contact->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($account);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$account}')]", "Account autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$account}')]")->click();
    }

    public function getAccount()
    {
        return $this->byXpath("//div[@id='s2id_orocrm_sales_opportunity_form_account']/a/span")->text();
    }

    public function setProbability($probability)
    {
        $this->probability->clear();
        $this->probability->value($probability);
        return $this;
    }

    public function getProbability()
    {
        return $this->probability->value();
    }

    public function seBudget($budget)
    {
        $this->budget->clear();
        $this->budget->value($budget);
        return $this;
    }

    public function getBudget()
    {
        return $this->budget->value();
    }

    public function setCustomerNeed($customerneed)
    {
        $this->customerneed->clear();
        $this->customerneed->value($customerneed);
        return $this;
    }

    public function getCustomerNeed()
    {
        return $this->customerneed->value();
    }

    public function setProposedSolution($proposedsolution)
    {
        $this->proposedsolution->clear();
        $this->proposedsolution->value($proposedsolution);
        return $this;
    }

    public function getPhone()
    {
        return $this->proposedsolution->value();
    }

    public function setCloseReason($closereason)
    {
        $this->closereason->selectOptionByLabel($closereason);
        return $this;
    }

    public function setCloseRevenue($closerevenu)
    {
        $this->closerevenu->clear();
        $this->closerevenu->value($closerevenu);
        return $this;
    }

    public function getCloseRevenue()
    {
        return $this->closerevenu->value();
    }

    public function setCloseDate($closedate)
    {
        $this->closedate->clear();
        $this->closedate->value($closedate);
        return $this;
    }

    public function getCloseDate()
    {
        return $this->closedate->value();
    }
//
//    public function setWebsite($website)
//    {
//        $this->website->clear();
//        $this->website->value($website);
//        return $this;
//    }
//
//    public function getWebsite()
//    {
//        return $this->website->value();
//    }
//
//    public function setEmployees($employees)
//    {
//        $this->employees->clear();
//        $this->employees->value($employees);
//        return $this;
//    }
//
//    public function getEmployees()
//    {
//        return $this->employees->value();
//    }
//
//    public function setAddressLabel($value)
//    {
//        $addresslabel = $this->byId("orocrm_sales_lead_form_address_label");
//        $addresslabel->clear();
//        $addresslabel->value($value);
//
//        return $this;
//    }
//
//    public function getAddressLabel()
//    {
//        $addresslabel = $this->byId("orocrm_sales_lead_form_address_label");
//        return $addresslabel->attribute('value');
//    }
//
//    public function setAddressStreet($value)
//    {
//        $addressstreet = $this->byId("orocrm_sales_lead_form_address_street");
//        $addressstreet->clear();
//        $addressstreet->value($value);
//
//        return $this;
//    }
//
//    public function getAddressStreet()
//    {
//        $addressstreet = $this->byId("orocrm_sales_lead_form_address_street");
//        return $addressstreet->attribute('value');
//    }
//
//    public function setAddressCity($value)
//    {
//        $addresscity = $this->byId("orocrm_sales_lead_form_address_city");
//        $addresscity->clear();
//        $addresscity->value($value);
//
//        return $this;
//    }
//
//    public function getAddressCity()
//    {
//        $addresscity = $this->byId("orocrm_sales_lead_form_address_city");
//        return $addresscity->attribute('value');
//    }
//
//    public function setAddressZipCode($value)
//    {
//        $addresscity = $this->byId("orocrm_sales_lead_form_address_postalCode");
//        $addresscity->clear();
//        $addresscity->value($value);
//
//        return $this;
//    }
//
//    public function getAddressZipCode()
//    {
//        $addresscity = $this->byId("orocrm_sales_lead_form_address_postalCode");
//        return $addresscity->attribute('value');
//    }
//
//    public function setAddressCountry($value)
//    {
//        $country = $this->byXpath("//div[@id='s2id_orocrm_sales_lead_form_address_country']/a");
//        $country->click();
//        $this->waitForAjax();
//        $this->byXpath("//div[@id='select2-drop']/div/input")->value($value);
//        $this->waitForAjax();
//        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$value}')]", "Country's autocoplete doesn't return search value");
//        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$value}')]")->click();
//        $this->waitForAjax();
//
//        return $this;
//    }
//
//    public function getAddressCountry()
//    {
//        return $this->byXpath("//div[@id = 's2id_orocrm_sales_lead_form_address_country']/a/span")->text();
//    }
//
//    public function setAddressState($value)
//    {
//        if ($this->byId("orocrm_sales_lead_form_address_state_text")->displayed()) {
//            $this->state = $this->byId("orocrm_sales_lead_form_address_state_text");
//            $this->state->clear();
//            $this->state->value($value);
//        } else {
//            $this->state = $this->byXpath("//div[@id='s2id_orocrm_sales_lead_form_address_state']/a");
//            $this->state->click();
//            $this->waitForAjax();
//            $this->byXpath("//div[@id='select2-drop']/div/input")->value($value);
//            $this->waitForAjax();
//            $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$value}')]", "Country's autocoplete doesn't return search value");
//            $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$value}')]")->click();
//        }
//
//        return $this;
//    }
//
//    public function getAddressState()
//    {
//        return $this->byXpath("//div[@id = 's2id_orocrm_sales_lead_form_address_state']/a/span")->text();
//    }
//
//    public function setAddress($data)
//    {
//        foreach ($data as $key => $value) {
//            $method = 'setAddress' . ucfirst($key);
//            $this->$method($value);
//        }
//
//        return $this;
//    }
//
//    public function getAddress(&$values)
//    {
//        $values['label'] = $this->getAddressLabel();
//        $values['street'] = $this->getAddressStreet();
//        $values['city'] = $this->getAddressCity();
//        $values['zipCode'] = $this->getAddressZipCode();
//        $values['country'] = $this->getAddressCountry();
//        $values['state'] = $this->getAddressState();
//
//        return $this;
//    }

    public function edit()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Update opportunity']")->click();
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
