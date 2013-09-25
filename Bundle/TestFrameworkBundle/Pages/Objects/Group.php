<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class Group extends AbstractEntity implements Entity
{

    protected $name;

    protected $roles;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->name = $this->byId('oro_user_group_form_name');
        $this->owner = $this->select($this->byId('oro_user_group_form_owner'));
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

    public function setOwner($owner)
    {
        $this->owner->selectOptionByLabel($owner);

        return $this;
    }

    public function getOwner()
    {
        return trim($this->owner->selectedLabel());
    }
}
