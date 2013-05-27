<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class Roles extends PageFilteredGrid
{
    const URL = 'user/role';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);

    }

    public function add()
    {
        $this->test->byXPath("//a[contains(., 'Create role')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new Role($this->test);
    }
}
