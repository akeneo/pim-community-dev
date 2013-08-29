<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class Role extends AbstractEntity implements Entity
{

    protected $name;

    protected $label;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->name = $this->byId('oro_user_role_form_role');
        $this->label = $this->byId('oro_user_role_form_label');
        $this->owner = $this->select($this->byId('oro_user_role_form_owner'));
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

    public function setOwner($owner)
    {
        $this->owner->selectOptionByLabel($owner);

        return $this;
    }

    public function getOwner()
    {
        return trim($this->owner->selectedLabel());
    }

    public function selectAcl($aclName)
    {
        $this->byXPath("//div[@id='acl_tree']//a[contains(., '$aclName')]/ins[@class='jstree-checkbox']")->click();
        return $this;
    }
}
