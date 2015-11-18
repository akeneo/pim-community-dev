<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity;

class Organization
{
    private $id;

    public function __construct($id = 0)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
