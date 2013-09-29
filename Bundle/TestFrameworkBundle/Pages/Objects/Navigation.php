<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use PHPUnit_Framework_Assert;
use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class Navigation extends Page
{
    protected $tabs;
    protected $menu;
    protected $pinbar;
    protected $xpathMenu = '';

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->tabs = $this->byId("main-menu");

        $this->pinbar = $this->byXPath("//div[contains(@class, 'pin-bar')]");
    }

    public function tab($tab)
    {
        $this->test->moveto($this->tabs->element($this->using('xpath')->value("ul/li/a[normalize-space(.) = '{$tab}']")));
        $this->xpathMenu = "//div[@id = 'main-menu']/ul" . "/li[a[normalize-space(.) = '{$tab}']]";
        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function menu($menu)
    {
        $this->test->moveto($this->byXPath($this->xpathMenu . "/ul/li/a[normalize-space(.) = '{$menu}']"));
        $this->xpathMenu = $this->xpathMenu . "/ul/li[a[normalize-space(.) = '{$menu}']]";

        return $this;
    }

    public function open()
    {
        $this->byXPath($this->xpathMenu . '/a')->click();

        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }
}
