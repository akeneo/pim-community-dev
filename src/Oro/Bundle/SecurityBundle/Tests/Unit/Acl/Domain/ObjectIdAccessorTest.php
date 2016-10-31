<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject;

class ObjectIdAccessorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIdPrefersInterfaceOverGetId()
    {
        $accessor = new ObjectIdAccessor();

        $obj = $this->createMock('Symfony\Component\Security\Acl\Model\DomainObjectInterface');
        $obj
            ->expects($this->once())
            ->method('getObjectIdentifier')
            ->will($this->returnValue('getObjectIdentifier()'));
        $obj
            ->expects($this->never())
            ->method('getId')
            ->will($this->returnValue('getId()'));

        $id = $accessor->getId($obj);

        $this->assertEquals('getObjectIdentifier()', $id);
    }

    public function testGetIdWithoutDomainObjectInterface()
    {
        $accessor = new ObjectIdAccessor();

        $id = $accessor->getId(new TestDomainObject());
        $this->assertEquals('getId()', $id);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testGetIdNull()
    {
        $accessor = new ObjectIdAccessor();

        $accessor->getId(null);
    }
}
