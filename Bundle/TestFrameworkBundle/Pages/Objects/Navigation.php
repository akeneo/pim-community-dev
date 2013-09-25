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
        $this->tabs = $this->byId("main-menu");

        $this->pinbar = $this->byXPath("//div[contains(@class, 'pin-bar')]");
    }

    public function tab($tab)
    {
        $this->test->moveto($this->tabs->element($this->using('xpath')->value("ul/li/a[normalize-space(.) = '{$tab}']")));
        $this->menu = $this->tabs->element($this->using('xpath')->value("ul/li[a[normalize-space(.) = '{$tab}']]/ul"));
        return $this;
    }

    public function menu($menu)
    {
        $this->test->moveto($this->menu->element($this->using('xpath')->value("li/a[normalize-space(.) = '{$menu}']")));
        $this->menu->element($this->using('xpath')->value("li/a[normalize-space(.) = '{$menu}']"))->click();

        try {
            $this->menu = $this->menu->element($this->using('xpath')->value("li[a[normalize-space(.) = '{$menu}']]/ul"));
        } catch (\Exception $e) {
            $this->menu = $this->menu->element($this->using('xpath')->value("li/a[normalize-space(.) = '{$menu}']"));
        }

        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }
}
