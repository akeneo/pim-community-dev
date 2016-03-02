<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Model;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Model\AclPermission;

class AclPermissionTest extends \PHPUnit_Framework_TestCase
{
    public function testAclPermission()
    {
        $obj = new AclPermission('TestName', AccessLevel::BASIC_LEVEL);
        $this->assertEquals('TestName', $obj->getName());
        $this->assertEquals(AccessLevel::BASIC_LEVEL, $obj->getAccessLevel());

        $obj->setName('AnotherName');
        $obj->setAccessLevel(AccessLevel::GLOBAL_LEVEL);
        $this->assertEquals('AnotherName', $obj->getName());
        $this->assertEquals(AccessLevel::GLOBAL_LEVEL, $obj->getAccessLevel());
    }
}
