<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class AclCheck extends Page
{
    public function __construct($testCase)
    {
        parent::__construct($testCase);
    }

    public function assertAcl($url, $title = '403 - Forbidden')
    {
        $this->url($url);
        $this->waitPageToLoad();
        $this->assertTitle($title, 'Page title is not that was expected');
        return $this;
    }
}
