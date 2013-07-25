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
        $this->tagname = $this->byId('oro_tag_tag_form_name');
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

    public function save()
    {
        $this->byXPath("//button[contains(., 'Save')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }
}
