<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Entity;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class User extends Page implements Entity
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

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

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

        return $this;
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

    public function setFirstname($name)
    {
        $this->first_name->clear();
        $this->first_name->value($name);
        return $this;
    }

    public function getFirstname()
    {
        return $this->first_name->value();
    }

    public function setLastname($name)
    {
        $this->last_name->clear();
        $this->last_name->value($name);
        return $this;
    }

    public function getLastname()
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

    public function setRoles($roles = array())
    {
        foreach ($roles as $role) {
            $this->roles->element($this->using('xpath')->value("div[label[text() = '{$role}']]/input"))->click();
        }

        return $this;

    }

    public function getRoles()
    {

    }

    public function setGroups($groups = array())
    {
        foreach ($groups as $group) {
            $this->groups->element($this->using('xpath')->value("div[label[text() = '{$group}']]/input"))->click();
        }

        return $this;
    }

    public function getGroups()
    {

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
        return new Users($this->test);
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
}
