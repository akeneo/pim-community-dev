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
        $this->label = $this->byId('oro_user_role_form_label');
        $this->owner = $this->select($this->byId('oro_user_role_form_owner'));
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

    /**
     * @param $entityName string of ACL resource name
     * @param $aclaction array of actions such as create, edit, delete, view, assign
     * @param $accesslevel
     * @return $this
     */
    public function setEntity($entityName, $aclaction, $accesslevel)
    {
        foreach ($aclaction as $action) {
            $action = strtoupper($action);
            $this->byXPath(
                "//div[strong/text() = '{$entityName}']/ancestor::tr//input" .
                "[contains(@name, '[$action][accessLevel')]/preceding-sibling::a"
            )->click();
            $this->waitForAjax();
            $this->accesslevel = $this->select(
                $this->byXpath(
                    "//div[strong/text() = '{$entityName}']/ancestor::tr//select" .
                    "[contains(@name, '[$action][accessLevel')]")
            );
            $this->accesslevel->selectOptionByLabel($accesslevel);
        }

        return $this;
    }

    /**
     * @param $capabilityname array of Capability ACL resources
     * @param $accesslevel
     * @return $this
     */
    public function setCapability($capabilityname, $accesslevel)
    {
        foreach ($capabilityname as $name) {
            $this->byXpath(
                "//div[strong/text() = '{$name}']/following-sibling::div//a"
            )->click();
            $this->waitForAjax();
            $this->accesslevel = $this->select($this->byXpath(
                "//div[strong/text() = '{$name}']/following-sibling::div//select")
            );
            $this->accesslevel->selectOptionByLabel($accesslevel);
        }

        return $this;
    }
}
