<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Fixture;


class Entity
{
    protected $owner;

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }
}
