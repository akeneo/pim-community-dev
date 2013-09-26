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
        $this->byXPath("//button[normalize-space(.) = 'Save and Close']")->click();
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

    public function verifyTag($tag)
    {
        if ($this->isElementPresent("//div[@id='s2id_orocrm_contact_form_tags_autocomplete']")) {
            $tagsPath = $this->byXpath("//div[@id='s2id_orocrm_contact_form_tags_autocomplete']//input");
            $tagsPath->click();
            $tagsPath->value(substr($tag, 0, (strlen($tag)-1)));
            $this->waitForAjax();
            $this->assertElementPresent(
                "//div[@id='select2-drop']//div[contains(., '{$tag}')]",
                "Tag's autocoplete doesn't return entity"
            );
            $tagsPath->clear();
        } else {
            if ($this->isElementPresent("//div[@id='tags-holder']")) {
                $this->assertElementPresent(
                    "//div[@id='tags-holder']//li[contains(., '{$tag}')]",
                    'Tag is not assigned to entity'
                );
            } else {
                throw new \Exception("Tag field can't be found");
            }
        }
        return $this;
    }

    /**
     * @param $tag
     * @return $this
     * @throws \Exception
     */
    public function setTag($tag)
    {
        if ($this->isElementPresent("//div[@id='s2id_orocrm_contact_form_tags_autocomplete']")) {
            $tagsPath = $this->byXpath("//div[@id='s2id_orocrm_contact_form_tags_autocomplete']//input");
            $tagsPath->click();
            $tagsPath->value($tag);
            $this->waitForAjax();
            $this->assertElementPresent(
                "//div[@id='select2-drop']//div[contains(., '{$tag}')]",
                "Tag's autocoplete doesn't return entity"
            );
            $this->byXpath("//div[@id='select2-drop']//div[contains(., '{$tag}')]")->click();

            return $this;
        } else {
            throw new \Exception("Tag field can't be found");
        }
    }
}
