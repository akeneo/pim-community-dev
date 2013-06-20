<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use PHPUnit_Framework_Assert;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Navigation extends Page
{
    protected $tabs;
    protected $menu;
    protected $pinbar;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->tabs = $this->byXPath("//div[@class = 'navbar application-menu']//ul[@class = 'nav nav-tabs']");

        $this->pinbar = $this->byXPath("//div[contains(@class, 'pin-bar')]");
    }

    public function tab($tab)
    {
        $this->tabs->element($this->using('xpath')->value("li/a[contains(., '{$tab}')]"))->click();
        $this->menu = $this->byXPath("//div[contains(@class, 'application-menu')]//div[@class = 'tab-content']/div[contains(@class, 'active')]/ul");
        return $this;
    }

    public function menu($menu)
    {
        $this->menu->element($this->using('xpath')->value("li/a[contains(., '{$menu}')]"))->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }
}
