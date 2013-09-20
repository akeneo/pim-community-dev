<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Sync;

use Doctrine\ORM\Query;
use Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures\TestEmailSynchronizationProcessor;

class AbstractEmailSynchronizationProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var TestEmailSynchronizationProcessor */
    private $processor;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $log;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $emailEntityBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $emailAddressManager;

    protected function setUp()
    {
        $this->log = $this->getMock('Psr\Log\LoggerInterface');
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailEntityBuilder = $this->getMockBuilder('Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailAddressManager = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new TestEmailSynchronizationProcessor(
            $this->log,
            $this->em,
            $this->emailEntityBuilder,
            $this->emailAddressManager
        );
    }

    public function testGetKnownEmailAddresses()
    {
        $q = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getArrayResult'))
            ->getMockForAbstractClass();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($qb));

        $qb->expects($this->once())
            ->method('select')
            ->with('a.email')
            ->will($this->returnValue($qb));
        $qb->expects($this->once())
            ->method('where')
            ->with('a.hasOwner = ?1')
            ->will($this->returnValue($qb));
        $qb->expects($this->once())
            ->method('setParameter')
            ->with(1, true)
            ->will($this->returnValue($qb));
        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($q));
        $q->expects($this->once())
            ->method('getArrayResult')
            ->will($this->returnValue(array(array('email' => 'test@test.com'))));

        $this->emailAddressManager->expects($this->once())
            ->method('getEmailAddressRepository')
            ->with($this->identicalTo($this->em))
            ->will($this->returnValue($repo));

        $result = $this->processor->callGetKnownEmailAddresses();
        $this->assertEquals(array('test@test.com'), $result);
    }
}
