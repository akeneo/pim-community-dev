<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity;

use Symfony\Component\Security\Acl\Model\DomainObjectInterface;

class TestEntityImplementsDomainObjectInterface implements DomainObjectInterface
{
    private $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getObjectIdentifier()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException();
        }

        return $this->id;
    }
}
