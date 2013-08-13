<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity\Provider;

use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProvider;

class EmailOwnerProviderTest extends \PHPUnit_Framework_TestCase
{
    private function getEmailOwnerProviderStorageMock(array $providers)
    {
        $storage = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderStorage');
        $storage->expects($this->any())
            ->method('getProviders')
            ->will($this->returnValue($providers));

        return $storage;
    }

    public function testFindEmailOwnerFirstProvider()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');
        $provider1 = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider1->expects($this->once())
            ->method('findEmailOwner')
            ->with($this->identicalTo($em), $this->equalTo('test'))
            ->will($this->returnValue($result));
        $provider2 = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider2->expects($this->never())
            ->method('findEmailOwner');

        $provider = new EmailOwnerProvider($this->getEmailOwnerProviderStorageMock(array($provider1, $provider2)));
        $this->assertEquals($result, $provider->findEmailOwner($em, 'test'));
    }

    public function testFindEmailOwnerSecondProvider()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');
        $provider1 = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider1->expects($this->once())
            ->method('findEmailOwner')
            ->with($this->identicalTo($em), $this->equalTo('test'))
            ->will($this->returnValue(null));
        $provider2 = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider2->expects($this->once())
            ->method('findEmailOwner')
            ->with($this->identicalTo($em), $this->equalTo('test'))
            ->will($this->returnValue($result));

        $provider = new EmailOwnerProvider($this->getEmailOwnerProviderStorageMock(array($provider1, $provider2)));
        $this->assertEquals($result, $provider->findEmailOwner($em, 'test'));
    }

    public function testFindEmailOwnerNotFound()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $provider1 = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider1->expects($this->once())
            ->method('findEmailOwner')
            ->with($this->identicalTo($em), $this->equalTo('test'))
            ->will($this->returnValue(null));
        $provider2 = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider2->expects($this->once())
            ->method('findEmailOwner')
            ->with($this->identicalTo($em), $this->equalTo('test'))
            ->will($this->returnValue(null));

        $provider = new EmailOwnerProvider($this->getEmailOwnerProviderStorageMock(array($provider1, $provider2)));
        $this->assertNull($provider->findEmailOwner($em, 'test'));
    }
}
