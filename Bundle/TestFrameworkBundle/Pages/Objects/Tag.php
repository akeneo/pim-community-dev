<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Entity;

class Tag extends AbstractEntity implements Entity
{
    protected $tagname;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function init($new = true)
    {
        if ($new) {
            $this->tagname = $this->byId('oro_tag_tag_form_name');
            $this->owner = $this->byXpath("//div[@id='s2id_oro_tag_tag_form_owner']/a");
        }
        return $this;
    }

    public function setTagname($accountname)
    {
        $this->tagname->clear();
        $this->tagname->value($accountname);
        return $this;
    }

    public function getTagname()
    {
        return $this->tagname->value();
    }

    public function setOwner($owner)
    {
        $this->owner->click();
        $this->waitForAjax();
        $this->byXpath("//div[@id='select2-drop']/div/input")->value($owner);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$owner}')]",
            "Owner autocoplete doesn't return search value"
        );
        $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$owner}')]")->click();

        return $this;

    }

    public function getOwner()
    {
        return;
    }

    public function save()
    {
        $this->byXPath("//button[contains(., 'Save')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }
}
