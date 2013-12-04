<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class TransactionEmail extends AbstractEntity implements Entity
{
    protected $entityname;
    protected $event;
    protected $template;
    protected $user;
    protected $groups;
    protected $email;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->entityname = $this->select($this->byId('emailnotification_entityName'));
        $this->event = $this->select($this->byId('emailnotification_event'));
        $this->template = $this->byXpath("//div[@id='s2id_emailnotification_template']/a");
        $this->user = $this->byXpath("//div[@id='s2id_emailnotification_recipientList_users']//input");
        $this->groups = $this->byId('emailnotification_recipientList_groups');
        $this->email = $this->byId('emailnotification_recipientList_email');
    }

    /**
     * @param $entityname
     * @return $this
     */
    public function setEntityName($entityname)
    {
        $this->entityname->selectOptionByLabel($entityname);
        $this->waitForAjax();
        return $this;
    }

    /**
     * @param $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event->selectOptionByLabel($event);
        return $this;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($template);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$template}')]",
            "Template autocoplete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$template}')]")->click();

        return $this;
    }

    /**
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user->click();
        $this->user->value($user);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$user}')]",
            "Users autocoplete field doesn't return entity"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$user}')]")->click();

        return $this;
    }

    /**
     * @param array $groups
     * @return $this
     */
    public function setGroups($groups = array())
    {
        foreach ($groups as $group) {
            $this->groups->element(
                $this->using('xpath')->value("div[label[normalize-space(text()) = '{$group}']]/input")
            )->click();
        }

        return $this;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email->clear();
        $this->email->value($email);

        return $this;
    }
}
