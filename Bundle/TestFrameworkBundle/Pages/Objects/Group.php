<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Entity;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Group extends Page implements Entity
{

    protected $name;

    protected $roles;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->name = $this->byId('oro_user_group_form_name');
        $this->roles = $this->select($this->byId('oro_user_group_form_roles'));
    }

    public function setName($name)
    {
        $this->name->value($name);
        return $this;
    }

    public function getName()
    {
        return $this->name->value();
    }

    public function setRoles($roles = array())
    {
        foreach ($roles as $role) {
            $this->roles->selectOptionByLabel($role);
        }

        return $this;
    }

    public function save()
    {
        $this->byXPath("//button[contains(., 'Save')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
        //return new Groups($this->test, false);
    }

    public function close()
    {
        $this->byXPath("//button[@class ='ui-dialog-titlebar-close']")->click();
        //support return to groups page only
        return new Groups($this->test, false);
    }
}
