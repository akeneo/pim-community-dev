<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages;

abstract class AbstractEntity extends Page
{
    /**
     * Save entity
     *
     * @return $this
     */
    public function save()
    {
        $this->byXPath("//button[contains(., 'Save and Close')]")->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        if ($this->assertElementPresent("//div[@class='customer-content pull-left']/div[1]//a")) {
            $this->byXpath("//div[@class='customer-content pull-left']/div[1]//a")->click();
            $this->waitPageToLoad();
            $this->waitForAjax();
        }

        return $this;
    }

    public function close($redirect = false)
    {
        $class = get_class($this) . 's';
        return new $class($this->test, $redirect);
    }
}
