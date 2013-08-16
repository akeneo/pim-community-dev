<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\TestEmailAddressProxy;

class EmailAddressManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testNewEmailAddress()
    {
        $manager = new EmailAddressManager('Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures', 'Test%sProxy');

        $this->assertEquals(new TestEmailAddressProxy(), $manager->newEmailAddress());
    }

    public function testGetEmailAddressRepository()
    {
        $manager = new EmailAddressManager('Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures', 'Test%sProxy');

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\TestEmailAddressProxy'))
            ->will($this->returnValue($repo));

        $this->assertTrue($repo === $manager->getEmailAddressRepository($em));
    }
}
