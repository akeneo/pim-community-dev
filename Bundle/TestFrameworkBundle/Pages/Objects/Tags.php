<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class Tags extends PageFilteredGrid
{
    const URL = 'tag';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    public function add($new = true)
    {
        $this->test->byXPath("//a[@title='Create tag')]")->click();
        //due to bug BAP-965
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        $tag = new Tag($this->test);
        return $tag->init($new);
    }

    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Tag($this->test);
    }

    public function edit()
    {
        $this->byXPath("//td[@class='action-cell']//a[contains(., '...')]")->click();
        $this->waitForAjax();
        $this->byXpath("//td[@class='action-cell']//a[@title= 'Update']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $tag = new Tag($this->test);

        return $tag->init();
    }

    public function delete()
    {
        $this->byXPath("//td[@class='action-cell']//a[contains(., '...')]")->click();
        $this->waitForAjax();
        $this->byXpath("//td[@class='action-cell']//a[@title= 'Delete']")->click();
        $this->waitForAjax();
        $this->byXPath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function checkContextMenu($tagname, $contextname)
    {
        $this->filterBy('Tag', $tagname);
        $this->byXPath("//td[@class='action-cell']//a[contains(., '...')]")->click();
        $this->waitForAjax();
        $this->assertElementNotPresent("//td[@class='action-cell']//a[@title= '{$contextname}']");
    }
}
