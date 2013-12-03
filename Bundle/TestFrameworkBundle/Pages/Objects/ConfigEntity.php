<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class ConfigEntity extends AbstractEntity implements Entity
{
    protected $name;
    protected $label;
    protected $plurallabel;
    protected $description;

    public function init($new = false)
    {
        if ($new) {
            $this->name = $this->byId('oro_entity_config_type_model_className');
        }
        $this->label = $this->byId('oro_entity_config_type_entity_label');
        $this->plurallabel = $this->byId('oro_entity_config_type_entity_plural_label');
        $this->description = $this->byId('oro_entity_config_type_entity_description');

        return $this;
    }

    public function setName($name)
    {
        $this->name->clear();
        $this->name->value($name);
        return $this;
    }

    public function getName()
    {
        return $this->name->value();
    }

    public function setLabel($label)
    {
        $this->label->clear();
        $this->label->value($label);
        return $this;
    }

    public function getLabel()
    {
        return $this->label->value();
    }

    public function setPluralLabel($plurallabel)
    {
        $this->plurallabel->clear();
        $this->plurallabel->value($plurallabel);
        return $this;
    }

    public function getPluralLabel()
    {
        return $this->plurallabel->value();
    }

    public function setDescription($description)
    {
        $this->description->clear();
        $this->description->value($description);
        return $this;
    }

    public function getDescription()
    {
        return $this->description->value();
    }

    public function createField()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title='Create field']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function setFieldName($fieldname)
    {
        $this->fieldname = $this->byId('oro_entity_extend_field_type_fieldName');
        $this->fieldname->clear();
        $this->fieldname->value($fieldname);
        return $this;
    }

    public function setType($type)
    {
        $this->type = $this->select($this->byId('oro_entity_extend_field_type_type'));
        $this->type->selectOptionByLabel($type);
        return $this;
    }

    public function edit()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[contains(., 'Edit')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->init();
        return $this;
    }

    public function proceed()
    {
        $this->byXPath("//div[@class='btn-group']/button[contains(., 'Continue')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function updateSchema()
    {
        $this->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title='Update schema']")->click();
        $this->byXPath("//div[@class='modal-footer']/a[contains(., 'Yes, Proceed')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function newCustomEntityAdd()
    {
        $this->byXPath("//div[@class='pull-right title-buttons-container']/a[contains(., 'Create')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function checkEntityField($fieldname)
    {
        $this->assertElementPresent(
            "//div[@class='control-group']/label[normalize-space(text()) = '{$fieldname}']",
            'Custom entity field not found'
        );

        return $this;
    }
}
