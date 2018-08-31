<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityRetrievalStrategy;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;

class ObjectIdentityRetrievalStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testGetObjectIdentityWithString()
    {
        $factory = $this->getMockBuilder(ObjectIdentityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $strategy = new ObjectIdentityRetrievalStrategy($factory);

        $result = new ObjectIdentity('id', 'type');
        $factory->expects($this->once())
            ->method('get')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($result));

        $this->assertTrue($result === $strategy->getObjectIdentity('test'));
    }

    public function testGetObjectIdentityWithObject()
    {
        $factory = $this->getMockBuilder(ObjectIdentityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $strategy = new ObjectIdentityRetrievalStrategy($factory);

        $obj = new \stdClass();
        $result = new ObjectIdentity('id', 'type');
        $factory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($obj))
            ->will($this->returnValue($result));

        $this->assertTrue($result === $strategy->getObjectIdentity($obj));
    }

    public function testGetObjectIdentityShouldCatchInvalidDomainObjectException()
    {
        $factory = $this->getMockBuilder(ObjectIdentityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $strategy = new ObjectIdentityRetrievalStrategy($factory);

        $obj = new \stdClass();
        $factory->expects($this->once())
            ->method('get')
            ->will($this->throwException(new InvalidDomainObjectException()));

        $this->assertNull($strategy->getObjectIdentity($obj));
    }
}
