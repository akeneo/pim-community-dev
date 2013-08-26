<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity;

class TestEntityWithOwnerFieldButWithoutGetOwnerMethod
{
    private $owner;

    public function __construct($owner)
    {
        $this->owner = $owner;
    }
}
