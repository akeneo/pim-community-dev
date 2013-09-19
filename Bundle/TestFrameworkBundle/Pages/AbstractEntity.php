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

        return $this;
    }

    public function toGrid()
    {
        $this->byXpath("//div[@class='customer-content pull-left']/div[1]//a")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function close($redirect = false)
    {
        $class = get_class($this);
        if (substr($class, -1) == 'y') {
            $class = substr($class, 0, strlen($class) - 1) . 'ies';
        } else {
            $class = $class . 's';
        }

        return new $class($this->test, $redirect);
    }
}
