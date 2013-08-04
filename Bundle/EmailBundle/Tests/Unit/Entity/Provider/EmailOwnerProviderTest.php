<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity\Provider;

use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProvider;

class EmailOwnerProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testFindEmailOwner1()
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


        $provider = new EmailOwnerProvider($em);
        $provider->addProvider($provider1);
        $provider->addProvider($provider2);
        $this->assertEquals($result, $provider->findEmailOwner('test'));
    }

    public function testFindEmailOwner2()
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


        $provider = new EmailOwnerProvider($em);
        $provider->addProvider($provider1);
        $provider->addProvider($provider2);
        $this->assertEquals($result, $provider->findEmailOwner('test'));
    }

    public function testFindEmailOwner3()
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


        $provider = new EmailOwnerProvider($em);
        $provider->addProvider($provider1);
        $provider->addProvider($provider2);
        $this->assertNull($provider->findEmailOwner('test'));
    }
}
