<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class Account extends AbstractEntity implements Entity
{
    protected $accountname;
    protected $street;
    protected $city;
    protected $zipcode;
    protected $country;
    protected $state;

    public function init()
    {
        $this->accountname = $this->byId('orocrm_account_form_name');
        $this->street = $this->byId('orocrm_account_form_billingAddress_street');
        $this->city = $this->byId('orocrm_account_form_billingAddress_city');
        $this->country = $this->byXpath("//div[@id='s2id_orocrm_account_form_billingAddress_country']/a");
        $this->zipcode = $this->byId('orocrm_account_form_billingAddress_postalCode');
        $this->owner = $this->byXpath("//div[@id='s2id_orocrm_account_form_owner']/a");

        if ($this->byId('orocrm_account_form_billingAddress_state_text')->displayed()) {
            $this->state = $this->byId('orocrm_account_form_billingAddress_state_text');
        } else {
            $this->state = $this->byXpath("//div[@id='s2id_orocrm_account_form_billingAddress_state']/a");
        }

        return $this;
    }

    public function setAccountName($accountname)
    {
        $this->accountname->clear();
        $this->accountname->value($accountname);
        return $this;
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

    public function verifyTag($tag)
    {
        if ($this->isElementPresent("//div[@id='s2id_orocrm_account_form_tags_autocomplete']")) {
            $this->tags = $this->byXpath("//div[@id='s2id_orocrm_account_form_tags_autocomplete']//input");
            $this->tags->click();
            $this->tags->value(substr($tag, 0, (strlen($tag)-1)));
            $this->waitForAjax();
            $this->assertElementPresent(
                "//div[@id='select2-drop']//div[contains(., '{$tag}')]",
                "Tag's autocoplete doesn't return entity"
            );
            $this->tags->clear();
        } else {
            if ($this->isElementPresent("//div[@id='tags-holder']")) {
                $this->assertElementPresent(
                    "//div[@id='tags-holder']//li[contains(., '{$tag}')]",
                    'Tag is not assigned to entity'
                );
            } else {
                throw new \Exception("Tag field can't be found");
            }
        }
        return $this;
    }

    /**
     * @param $tag
     * @return $this
     * @throws \Exception
     */
    public function setTag($tag)
    {
        if ($this->isElementPresent("//div[@id='s2id_orocrm_account_form_tags_autocomplete']")) {
            $this->tags = $this->byXpath("//div[@id='s2id_orocrm_account_form_tags_autocomplete']//input");
            $this->tags->click();
            $this->tags->value($tag);
            $this->waitForAjax();
            $this->assertElementPresent(
                "//div[@id='select2-drop']//div[contains(., '{$tag}')]",
                "Tag's autocoplete doesn't return entity"
            );
            $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$tag}')]")->click();

            return $this;
        } else {
            throw new \Exception("Tag field can't be found");
        }
    }

    public function getAccountName()
    {
        return $this->accountname->value();
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

    public function setCountry($country)
    {
        $this->country->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($country);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$country}')]",
            "Country's autocoplete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$country}')]")->click();
        $this->waitForAjax();

        return $this;
    }

    public function setState($state)
    {
        if ($this->byId('orocrm_account_form_billingAddress_state_text')->displayed()) {
            $this->state = $this->byId('orocrm_account_form_billingAddress_state_text');
        } else {
            $this->state = $this->byXpath("//div[@id='s2id_orocrm_account_form_billingAddress_state']/a");
        }

        $this->state->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($state);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$state}')]",
            "Country's autocoplete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$state}')]")->click();

        return $this;
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
