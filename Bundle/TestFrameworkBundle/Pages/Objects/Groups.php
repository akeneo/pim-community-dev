<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\PageFilteredGrid;

class Groups extends PageFilteredGrid
{
    const URL = 'user/group';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);

    }

    public function add()
    {
        $this->test->byXPath("//a[contains(., 'Create group')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new Group($this->test);
    }
}
