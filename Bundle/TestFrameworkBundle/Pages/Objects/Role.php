<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Entity;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Role extends Page implements Entity
{

    protected $name;

    protected $label;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->name = $this->byId('oro_user_role_form_role');
        $this->label = $this->byId('oro_user_role_form_label');
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

    public function setLabel($label)
    {
        $this->label->value($label);
        return $this;
    }

    public function getLabel()
    {
        return $this->label->value();
    }

    public function selectAcl($aclName)
    {
        $this->byXPath("//div[@id='acl_tree']//a[contains(., '$aclName')]/ins[@class='jstree-checkbox']")->click();
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
        return new Roles($this->test, false);
    }
}
