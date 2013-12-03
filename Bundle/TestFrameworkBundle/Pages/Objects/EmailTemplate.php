<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class EmailTemplate extends AbstractEntity implements Entity
{
    protected $entityname;
    protected $name;
    protected $type;
    protected $subject;
    protected $content;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->entityname = $this->select($this->byId('oro_email_emailtemplate_entityName'));
        $this->name = $this->byId('oro_email_emailtemplate_name');
        $this->type = $this->byId('oro_email_emailtemplate_type');
        $this->subject = $this->byId('oro_email_emailtemplate_translations_defaultLocale_en_subject');
        $this->content = $this->byId('oro_email_emailtemplate_translations_defaultLocale_en_content');
    }

    /**
     * @param $entityname
     * @return $this
     */
    public function setEntityName($entityname)
    {
        $this->entityname->selectOptionByLabel($entityname);
        return $this;
    }

    public function getEntityName()
    {
        return $this->entityname->selectedLabel();
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name->clear();
        $this->name->value($name);
        return $this;
    }

    public function getName()
    {
        return $this->name->attribute('value');
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type->element(
            $this->using('xpath')->value("div[label[normalize-space(text()) = '{$type}']]/input")
        )->click();
        return $this;
    }

    public function getType()
    {
        return $this->byXPath(
            "//div[@id='oro_email_emailtemplate_type']/div[input[@checked = 'checked']]/label"
        )->text();
    }

    /**
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject->clear();
        $this->subject->value($subject);
        return $this;
    }

    public function getSubject()
    {
        return $this->subject->attribute('value');
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content->clear();
        $this->content->value($content);
        return $this;
    }

    public function getContent()
    {
        return $this->content->attribute('value');
    }

    public function getFields(&$values)
    {
        $values['entityname'] = $this->getEntityName();
        $values['type'] = $this->getType();
        $values['name'] = $this->getName();
        $values['subject'] = $this->getSubject();
        $values['content'] = $this->getContent();

        return $this;
    }
}
