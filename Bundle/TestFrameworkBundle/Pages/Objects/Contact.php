<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class Contact extends AbstractEntity implements Entity
{
    protected $nameprefix;
    protected $firstname;
    protected $lastname;
    protected $namesuffix;
    protected $email;
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
        $this->addressCollection = $this->byId('orocrm_contact_form_addresses_collection');

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

    public function verifyTag($tag)
    {
        if ($this->isElementPresent("//div[@id='s2id_orocrm_contact_form_tags']")) {
            $this->tags = $this->byXpath("//div[@id='s2id_orocrm_contact_form_tags']//input");
            $this->tags->click();
            $this->tags->value(substr($tag, 0, (strlen($tag)-1)));
            $this->waitForAjax();
            $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$tag}')]", "Tag's autocoplete doesn't return entity");
            $this->tags->clear();
        } else {
            if ($this->isElementPresent("//div[@id='tags-holder']")) {
                $this->assertElementPresent("//div[@id='tags-holder'][contains(., '{$tag}')]", 'Tag is not assigned to entity');
            } else {
                throw new \Exception("Tag field can't be found");
            }
        }
        return $this;
    }

    public function setTag($tag)
    {
        $this->isElementPresent("//div[@id='s2id_orocrm_contact_form_tagss']");
        $this->tags = $this->byXpath("//div[@id='s2id_orocrm_contact_form_tags']//input");
        $this->tags->click();
        $this->tags->value($tag);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$tag}')]", "Tag's autocoplete doesn't return entity");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$tag}')]")->click();

        return $this;
    }

    public function setAddressTypes($values, $addressId = 0)
    {
        foreach ($values as $type) {
            $this->byXpath("//input[@name = 'orocrm_contact_form[addresses][{$addressId}][types][]' and @value = '{$type}']")->click();
        }

        return $this;
    }

    public function setAddressPrimary($value, $addressId = 0)
    {
        if ($value) {
            $primary = $this->byId("orocrm_contact_form_addresses_{$addressId}_primary");
            $primary->click();
        }

        return $this;
    }

    public function getAddressTypes($addressId)
    {
        $values = array();
        $types = $this->elements($this->using('xpath')->value("//input[@name = 'orocrm_contact_form[addresses][{$addressId}][types][]']"));
        foreach ($types as $type) {
            if ($type->selected()) {
                $values[] = $type->attribute('value');
            }
        }

        return $values;
    }

    public function getAddressPrimary($addressId = 0)
    {
        return $this->byId("orocrm_contact_form_addresses_{$addressId}_primary")->selected();
    }

    public function setAddressFirstName($value, $addressId = 0)
    {
        $addressFirstName = $this->byId("orocrm_contact_form_addresses_{$addressId}_firstName");
        $addressFirstName->clear();
        $addressFirstName->value($value);

        return $this;

    }

    public function getAddressFirstName($addressId = 0)
    {
        $addressFirstName = $this->byId("orocrm_contact_form_addresses_{$addressId}_firstName");
        return $addressFirstName->attribute('value');
    }

    public function setAddressLastName($value, $addressId = 0)
    {
        $addressLastName = $this->byId("orocrm_contact_form_addresses_{$addressId}_lastName");
        $addressLastName->clear();
        $addressLastName->value($value);

        return $this;

    }

    public function getAddressLastName($addressId = 0)
    {
        $addressLastName = $this->byId("orocrm_contact_form_addresses_{$addressId}_lastName");
        return $addressLastName->attribute('value');
    }

    public function setAddressStreet($value, $addressId = 0)
    {
        $street = $this->byId("orocrm_contact_form_addresses_{$addressId}_street");
        $street->clear();
        $street->value($value);

        return $this;
    }

    public function getAddressStreet($addressId = 0)
    {
        $street = $this->byId("orocrm_contact_form_addresses_{$addressId}_street");
        return $street->attribute('value');
    }

    public function setAddressCity($value, $addressId = 0)
    {
        $city = $this->byId("orocrm_contact_form_addresses_{$addressId}_city");
        $city->clear();
        $city->value($value);
        return $this;
    }

    public function getAddressCity($addressId = 0)
    {
        $city = $this->byId("orocrm_contact_form_addresses_{$addressId}_city");
        return $city->attribute('value');
    }

    public function setAddressPostalCode($value, $addressId = 0)
    {
        $zipcode = $this->byId("orocrm_contact_form_addresses_{$addressId}_postalCode");
        $zipcode->clear();
        $zipcode->value($value);
        return $this;
    }

    public function getAddressPostalCode($addressId = 0)
    {
        $zipcode = $this->byId("orocrm_contact_form_addresses_{$addressId}_postalCode");
        return $zipcode->attribute('value');
    }

    public function setAddressCountry($value, $addressId = 0)
    {
        $country = $this->byXpath("//div[@id='s2id_orocrm_contact_form_addresses_{$addressId}_country']/a");
        $country->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($value);
        $this->waitForAjax();
        $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$value}')]", "Country's autocoplete doesn't return search value");
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$value}')]")->click();
        $this->waitForAjax();

        return $this;
    }

    public function getAddressCountry($addressId = 0)
    {
        return $this->byXpath("//div[@id = 's2id_orocrm_contact_form_addresses_{$addressId}_country']/a/span")->text();
    }

    public function setAddressState($state, $addressId = 0)
    {
        if ($this->byId("orocrm_contact_form_addresses_{$addressId}_state_text")->displayed()) {
            $this->state = $this->byId("orocrm_contact_form_addresses_{$addressId}_state_text");
            $this->state->clear();
            $this->state->value($state);
        } else {
            $this->state = $this->byXpath("//div[@id='s2id_orocrm_contact_form_addresses_{$addressId}_state']/a");
            $this->state->click();
            $this->waitForAjax();
            $this->byXpath("//div[@id='select2-drop']/div/input")->value($state);
            $this->waitForAjax();
            $this->assertElementPresent("//div[@id='select2-drop']//div[contains(., '{$state}')]", "Country's autocoplete doesn't return search value");
            $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$state}')]")->click();
        }

        return $this;
    }

    public function getAddressState($addressId = 0)
    {
        return $this->byXpath("//div[@id = 's2id_orocrm_contact_form_addresses_{$addressId}_state']/a/span")->text();
    }

    public function setAddress($data, $addressId = 0)
    {
        if (!$this->isElementPresent(
            "//div[@id='orocrm_contact_form_addresses_collection']/div[@data-content='{$addressId}' or " .
            "@data-content='orocrm_contact_form[addresses][{$addressId}]']"
        )
        ) {
            //click Add
            $this->byXpath("//a[@class='btn add-list-item']")->click();
            $this->waitForAjax();
        }

        foreach ($data as $key => $value) {
            $method = 'setAddress' . ucfirst($key);
            $this->$method($value, $addressId);
        }

        return $this;
    }

    public function getAddress(&$values, $addressId = 0)
    {
        $values['types'] = $this->getAddressTypes($addressId);
        $values['primary'] = $this->getAddressPrimary($addressId);
        $values['firstName'] = $this->getAddressFirstName($addressId);
        $values['lastName'] = $this->getAddressLastName($addressId);
        $values['street'] = $this->getAddressStreet($addressId);
        $values['city'] = $this->getAddressCity($addressId);
        $values['postalCode'] = $this->getAddressPostalCode($addressId);
        $values['country'] = $this->getAddressCountry($addressId);
        $values['state'] = $this->getAddressState($addressId);

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
