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
    protected $owner;

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
        $this->closedate = $this->byId('date_selector_orocrm_sales_opportunity_form_closeDate');
        $this->owner = $this->byXpath("//div[@id='s2id_orocrm_sales_opportunity_form_owner']/a");

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
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$contact}')]",
            "Contact autocoplete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$contact}')]")->click();

        return $this;
    }

    public function getContact()
    {
        return $this->byXpath(
            "//div[@id='s2id_orocrm_sales_opportunity_form_contact']/a/span"
        )->text();
    }

    public function setAccount($account)
    {
        $this->account->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($account);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$account}')]",
            "Account autocoplete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$account}')]")->click();

        return $this;
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
