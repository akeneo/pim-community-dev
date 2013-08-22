<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures;

class TestObjectWithOwnerFieldButWithoutGetOwnerMethod
{
    private $owner;

    public function __construct($owner)
    {
        $this->owner = $owner;
    }
}
