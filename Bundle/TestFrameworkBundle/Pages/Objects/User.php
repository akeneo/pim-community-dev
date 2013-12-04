<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class User extends AbstractEntity implements Entity
{
    protected $username;
    protected $enabled;
    protected $first_password;
    protected $second_password;
    protected $first_name;
    protected $last_name;
    protected $email;
    protected $dob;
    protected $avatar;
    protected $groups;
    protected $roles;

    protected $company;
    protected $salary;
    protected $address;
    protected $middlename;
    protected $gender;
    protected $website;

    public function init($new = false)
    {
        $this->username = $this->byId('oro_user_user_form_username');
        if ($new) {
            $this->first_password = $this->byId('oro_user_user_form_plainPassword_first');
            $this->second_password = $this->byId('oro_user_user_form_plainPassword_second');
        }
        $this->enabled = $this->select($this->byId('oro_user_user_form_enabled'));
        $this->first_name = $this->byId('oro_user_user_form_firstName');
        $this->last_name = $this->byId('oro_user_user_form_lastName');
        $this->email = $this->byId('oro_user_user_form_email');
        $this->groups = $this->byId('oro_user_user_form_groups');
        $this->roles = $this->byId('oro_user_user_form_rolesCollection');
        $this->owner = $this->select($this->byId('oro_user_user_form_owner'));

        return $this;
    }

    public function setOwner($owner)
    {
        $this->owner->selectOptionByLabel($owner);

        return $this;
    }

    public function getOwner()
    {
        return trim($this->owner->selectedLabel());
    }

    public function setUsername($name)
    {
        $this->username->clear();
        $this->username->value($name);
        return $this;
    }

    public function getName()
    {
        return $this->username->value();
    }

    public function enable()
    {
        $this->enabled->selectOptionByLabel('Active');
        return $this;
    }

    public function disable()
    {
        $this->enabled->selectOptionByLabel('Inactive');
        return $this;
    }

    public function setFirstpassword($password)
    {
        $this->first_password->clear();
        $this->first_password->value($password);
        return $this;
    }

    public function getFirstpassword()
    {
        return $this->first_password->value();
    }

    public function setSecondpassword($password)
    {
        $this->second_password->clear();
        $this->second_password->value($password);
        return $this;
    }

    public function getSecondpassword()
    {
        return $this->second_password->value();
    }

    public function setFirstName($name)
    {
        $this->first_name->clear();
        $this->first_name->value($name);
        return $this;
    }

    public function getFirstName()
    {
        return $this->first_name->value();
    }

    public function setLastName($name)
    {
        $this->last_name->clear();
        $this->last_name->value($name);
        return $this;
    }

    public function getLastName()
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
        return $this->email->value();
    }

    public function verifyTag($tag)
    {
        if ($this->isElementPresent("//div[@id='s2id_oro_user_user_form_tags_autocomplete']")) {
            $this->tags = $this->byXpath("//div[@id='s2id_oro_user_user_form_tags_autocomplete']//input");
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
        if ($this->isElementPresent("//div[@id='s2id_oro_user_user_form_tags_autocomplete']")) {
            $this->tags = $this->byXpath("//div[@id='s2id_oro_user_user_form_tags_autocomplete']//input");
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

    public function setRoles($roles = array())
    {
        foreach ($roles as $role) {
            $this->roles->element(
                $this->using('xpath')->value("div[label[normalize-space(text()) = '{$role}']]/input")
            )->click();
        }

        return $this;

    }

    public function getRoles()
    {

    }

    public function setGroups($groups = array())
    {
        foreach ($groups as $group) {
            $this->groups->element(
                $this->using('xpath')->value("div[label[normalize-space(text()) = '{$group}']]/input")
            )->click();
        }

        return $this;
    }

    public function getGroups()
    {

    }

    public function edit()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit user']")->click();
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
        return new Users($this->test, false);
    }

    public function viewInfo($userName)
    {
        $this->byXPath("//ul[@class='nav pull-right user-menu']//a[@class='dropdown-toggle']")->click();
        $this->waitForAjax();
        $this->byXpath("//ul[@class='dropdown-menu']//a[contains(normalize-space(.), 'My User')]")->click();
        $this->waitPageToLoad();
        $this->assertElementPresent(
            "//div[label[normalize-space(text()) = 'User name']]//div/p[normalize-space(text()) = '$userName']"
        );
        return $this;
    }

    public function checkRoleSelector()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit profile']")->click();
        $this->waitPageToLoad();
        $this->assertElementPresent(
            "//div[@id='oro_user_user_form_rolesCollection']//input[@checked='checked' and @disabled='disabled']",
            'Role selector are not disabled for user'
        );
    }

    public function checkHistoryWindow()
    {
        $this->byXpath(
            "//div[@class='navigation clearfix navbar-extra navbar-extra-right']//a[contains(., 'Change History')]"
        )->click();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@class='ui-dialog ui-widget ui-widget-content ui-corner-all ".
            "ui-front ui-draggable ui-resizable ui-dialog-normal ui-dialog-buttons']"
        );
        $this->byXpath(
            "//div[@class='ui-dialog-titlebar-buttonpane']/button[@title='close']"
        )->click();
        $this->waitForAjax();
        $this->assertElementNotPresent(
            "//div[@class='ui-dialog ui-widget ui-widget-content ui-corner-all " .
            "ui-front ui-draggable ui-resizable ui-dialog-normal ui-dialog-buttons']"
        );

        return $this;
    }
}
