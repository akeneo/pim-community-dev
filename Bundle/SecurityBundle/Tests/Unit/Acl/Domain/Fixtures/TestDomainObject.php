<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures;

class TestDomainObject
{
    public function getObjectIdentifier()
    {
        return 'getObjectIdentifier()';
    }

    public function getId()
    {
        return 'getId()';
    }

    protected function someProtectedMethod()
    {
        return 'someProtectedMethod()';
    }
}
