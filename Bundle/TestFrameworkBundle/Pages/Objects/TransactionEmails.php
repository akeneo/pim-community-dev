<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class TransactionEmails extends PageFilteredGrid
{
    const URL = 'notification/email';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return TransactionsEmail
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create notification rule']")->click();
        //due to bug BAP-965
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new TransactionEmail($this->test);
    }

    /**
     * @param array $entityData
     * @return TransactionsEmail
     */
    public function open($entityData = array())
    {
        $transactionemail = $this->getEntity($entityData);
        $transactionemail->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new TransactionEmail($this->test);
    }

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
