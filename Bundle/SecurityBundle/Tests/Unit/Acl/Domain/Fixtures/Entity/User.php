<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity;

class User
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

    public function getOwner()
    {
        return $this->owner;
    }
}
