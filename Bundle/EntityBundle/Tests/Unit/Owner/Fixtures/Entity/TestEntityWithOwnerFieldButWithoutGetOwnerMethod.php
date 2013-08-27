<?php

namespace Oro\Bundle\EntityBundle\Tests\Unit\Owner\Fixtures\Entity;

class TestEntityWithOwnerFieldButWithoutGetOwnerMethod
{
    private $owner;

    public function __construct($owner)
    {
        $this->owner = $owner;
    }
}
