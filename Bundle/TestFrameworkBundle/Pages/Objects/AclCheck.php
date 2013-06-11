<?php

namespace Oro\Bundle\TestFrameworkBundle\Pages\Objects;

use Oro\Bundle\TestFrameworkBundle\Pages\Page;

class AclCheck extends Page
{
    public function __construct($testCase)
    {
        parent::__construct($testCase);
    }

    public function checkFor403($currentUrl)
    {
        $this->url($currentUrl);
        $this->assertTitle('403 - Forbidden', 'Page is allowed to User, ACL do not work');
        return $this;
    }
}
