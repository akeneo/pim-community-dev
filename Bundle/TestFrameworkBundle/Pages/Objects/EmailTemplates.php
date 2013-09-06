<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class EmailTemplates extends PageFilteredGrid
{
    const URL = 'email/emailtemplate';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return EmailTemplate
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create template')]")->click();
        //due to bug BAP-965
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new EmailTemplate($this->test);
    }

    /**
     * @param array $entityData
     * @return EmailTemplate
     */
    public function open($entityData = array())
    {
        $emailtemplate = $this->getEntity($entityData);
        $emailtemplate->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new EmailTemplate($this->test);
    }

    /**
     * @param $filterby
     * @param $entityname
     * @return $this
     */
    public function delete($filterby, $entityname)
    {
        $this->filterBy($filterby, $entityname);
        $this->waitForAjax();
        $this->byXPath("//td[@class='action-cell']//a[contains(., '...')]")->click();
        $this->waitForAjax();
        $this->byXpath("//td[@class='action-cell']//a[@title= 'Delete']")->click();
        $this->waitForAjax();
        $this->byXPath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function cloneEntity($filterby, $entityname)
    {
        $this->filterBy($filterby, $entityname);
        $this->waitForAjax();
        $this->byXPath("//td[@class='action-cell']//a[contains(., '...')]")->click();
        $this->waitForAjax();
        $this->byXpath("//td[@class='action-cell']//a[@title= 'Clone']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new EmailTemplate($this->test);
    }

    /**
     * @param $entityname
     * @param $contextname
     * @return $this
     */
    public function checkContextMenu($entityname, $contextname)
    {
        $this->filterBy('Recipient email', $entityname);
        $this->waitForAjax();
        if ($this->isElementPresent("//td[@class='action-cell']//a[contains(., '...')]")) {
            $this->byXPath("//td[@class='action-cell']//a[contains(., '...')]")->click();
            $this->waitForAjax();
            return $this->assertElementNotPresent("//td[@class='action-cell']//a[@title= '{$contextname}']");
        }

        return $this;
    }
}
