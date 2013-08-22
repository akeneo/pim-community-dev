<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures;

class Organization
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
