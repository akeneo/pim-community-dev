<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner\Fixtures\Entity;

class TestEntity
{
    private $id;

    private $owner;

    public function __construct($id = 0, $owner = null)
    {
        $this->id = $id;
        $this->owner = $owner;
    }

    public function getId()
    {
        return $this->id;
    }
}
