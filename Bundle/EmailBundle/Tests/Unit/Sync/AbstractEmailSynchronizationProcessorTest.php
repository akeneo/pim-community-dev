<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Sync;

use Doctrine\ORM\Query;
use Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\TestEmailAddressProxy;
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
        $emailAddress = new TestEmailAddressProxy();
        $emailAddress->setEmail('test@test.com');

        $q = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
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
            ->with('partial a.{id, email, updated}')
            ->will($this->returnValue($qb));
        $qb->expects($this->once())
            ->method('where')
            ->with('a.hasOwner = ?1')
            ->will($this->returnValue($qb));
        $qb->expects($this->once())
            ->method('orderBy')
            ->with('a.updated', 'DESC')
            ->will($this->returnValue($qb));
        $qb->expects($this->once())
            ->method('setParameter')
            ->with(1, true)
            ->will($this->returnValue($qb));
        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($q));
        $q->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue(array($emailAddress)));

        $this->emailAddressManager->expects($this->once())
            ->method('getEmailAddressRepository')
            ->with($this->identicalTo($this->em))
            ->will($this->returnValue($repo));

        $result = $this->processor->callGetKnownEmailAddresses();
        $this->assertCount(1, $result);
        $this->assertEquals('test@test.com', $result[0]->getEmail());
    }
}
