<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

/**
 * Class Contact
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Contact extends AbstractEntity implements Entity
{
    protected $nameprefix;
    protected $firstname;
    protected $lastname;
    protected $namesuffix;
    protected $email;
    protected $assignedto;
    protected $reportsto;
    protected $addressCollection;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function init()
    {
        $this->nameprefix = $this->byId('orocrm_contact_form_namePrefix');
        $this->firstname = $this->byId('orocrm_contact_form_firstName');
        $this->lastname = $this->byId('orocrm_contact_form_lastName');
        $this->namesuffix = $this->byId('orocrm_contact_form_nameSuffix');
        $this->email = $this->byId('orocrm_contact_form_emails_0_email');
        $this->assignedto = $this->byXpath("//div[@id='s2id_orocrm_contact_form_assignedTo']/a");
        $this->reportsto = $this->byXpath("//div[@id='s2id_orocrm_contact_form_reportsTo']/a");
        $this->addressCollection = $this->byId('orocrm_contact_form_addresses_collection');
        $this->owner = $this->byXpath("//div[@id='s2id_orocrm_contact_form_owner']/a");

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

    public function setOwner($owner)
    {
        $this->moveto($this->owner);
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

    public function setAddressTypes($values, $addressId = 0)
    {
        $xpath = "//input[@name = 'orocrm_contact_form[addresses][{$addressId}][types][]'";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpath = "//input[@name='orocrm_contact_address_form[types][]'";
        }
        foreach ($values as $type) {
            $this->byXpath("{$xpath} and @value = '{$type}']")->click();
        }

        return $this;
    }

    public function setAddressPrimary($value, $addressId = 0)
    {
        $primary = "//input[@id='orocrm_contact_form_addresses_{$addressId}_primary']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $primary = ("//input[@id='orocrm_contact_address_form_primary']");
        }
        if ($value) {
            $this->byXpath($primary)->click();
        }

        return $this;
    }

    public function getAddressTypes($addressId)
    {
        $values = array();
        $types = $this->elements(
            $this->using('xpath')->value("//input[@name = 'orocrm_contact_form[addresses][{$addressId}][types][]']")
        );
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
        $addressFirstName = "//input[@id='orocrm_contact_form_addresses_{$addressId}_firstName']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $addressFirstName = "//input[@id='orocrm_contact_address_form_firstName']";
        }
        $addressFirstName = $this->byXpath($addressFirstName);
        $this->moveto($addressFirstName);

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
        $addressLastName = "//input[@id='orocrm_contact_form_addresses_{$addressId}_lastName']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $addressLastName = "//input[@id='orocrm_contact_address_form_lastName']";
        }
        $addressLastName = $this->byXpath($addressLastName);
        $this->moveto($addressLastName);

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
        $street = "//input[@id='orocrm_contact_form_addresses_{$addressId}_street']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $street = "//input[@id='orocrm_contact_address_form_street']";
        }
        $street = $this->byXpath($street);
        $this->moveto($street);

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
        $xpathCity = "//input[@id='orocrm_contact_form_addresses_{$addressId}_city']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpathCity = "//input[@id='orocrm_contact_address_form_city']";
        }
        $city = $this->byXpath($xpathCity);
        $this->moveto($city);

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
        $xpathZipcode = "//input[@id='orocrm_contact_form_addresses_{$addressId}_postalCode']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpathZipcode = "//input[@id='orocrm_contact_address_form_postalCode']";
        }
        $zipcode = $this->byXpath($xpathZipcode);
        $this->moveto($zipcode);

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
        $country = "//div[@id='s2id_orocrm_contact_form_addresses_{$addressId}_country']/a";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $country = "//div[@id='s2id_orocrm_contact_address_form_country']/a";
        }
        $country = $this->byXpath($country);
        $this->moveto($country);

        $country->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($value);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$value}')]",
            "Country's autocoplete doesn't return search value"
        );
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
        //$xpath = "//input[@id='orocrm_contact_form_addresses_0_state_text']";
        //if  ($this->isElementPresent("//div[@role='dialog']")) {
        //    $xpath = "//input[@id='orocrm_contact_address_form_state_text']";
        //}
        //$this->byXpath($xpath)->clear();
        //$this->byXpath($xpath)->value($state);

        $xpath = "//div[@id='s2id_orocrm_contact_form_addresses_{$addressId}_state']/a";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpath = "//div[@id='s2id_orocrm_contact_address_form_state']/a";
        }
        $xpath = $this->byXpath($xpath);
        $this->moveto($xpath);

        $xpath->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($state);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$state}')]",
            "Country's autocopmlete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$state}')]")->click();

        return $this;
    }

    public function getAddressState($addressId = 0)
    {
        return $this->byXpath("//div[@id = 's2id_orocrm_contact_form_addresses_{$addressId}_state']/a/span")->text();
    }

    public function setAddress($data, $addressId = 0)
    {
        if ($this->isElementPresent("//button[@data-action-name='add_address']")) {
            // click Add address button
            $this->byXpath("//button[@data-action-name='add_address']")->click();
            $this->waitForAjax();
        } elseif (!$this->isElementPresent(
            "//div[@id='orocrm_contact_form_addresses_collection']/div[@data-content='{$addressId}' or " .
            "@data-content='orocrm_contact_form[addresses][{$addressId}]']"
        )
        ) {
            //click Add
            $addButton = $this->byXpath(
                "//div[@class='row-oro'][div[@id='orocrm_contact_form_addresses_collection']]" .
                "//a[@class='btn add-list-item']"
            );
            $this->moveto($addButton);
            $addButton->click();
            $this->waitForAjax();
        }

        foreach ($data as $key => $value) {
            $method = 'setAddress' . ucfirst($key);
            $this->$method($value, $addressId);
        }

        if ($this->isElementPresent("//div[@role='dialog']")) {
            $this->byXpath("//div[@class='form-actions widget-actions']//button[@type='submit']")->click();
            $this->waitForAjax();
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
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$assignedto}')]",
            "Assigned to autocoplete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$assignedto}')]")->click();

        return $this;
    }

    public function setReportsTo($reportsto)
    {
        $this->reportsto->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($reportsto);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$reportsto}')]",
            "Reports to autocoplete doesn't return search value"
        );
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
