<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\EventListener;

use Pim\Bundle\BatchBundle\EventListener\UpdateJobStatusSubscriber;
use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * Related class test
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateJobStatusSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->validator = $this->getValidatorMock();
        $this->listener = new UpdateJobStatusSubscriber($this->validator);
    }

    public function testInstanceOfSubscriber()
    {
        $this->assertInstanceOf('Doctrine\Common\EventSubscriber', $this->listener);
    }

    public function testSubsribedEvents()
    {
        $this->assertEquals(array('prePersist', 'preUpdate'), $this->listener->getSubscribedEvents());
    }

    public function testOnlySupportJob()
    {
        $entity = $this->getEntityMock('stdClass');
        $event = $this->getEventMock($entity);

        $this->validator
            ->expects($this->never())
            ->method('validate');

        $this->listener->prePersist($event);
    }

    public function testSuccessfullValidation()
    {
        $entity     = $this->getEntityMock('Pim\Bundle\BatchBundle\Entity\Job');
        $event      = $this->getEventMock($entity);
        $violations = $this->getViolationsMock(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($entity, array('Default', 'Configuration'))
            ->will($this->returnValue($violations));

        $entity->expects($this->once())
            ->method('setStatus')
            ->with(Job::STATUS_READY);

        $this->listener->prePersist($event);
    }

    public function testFailValidation()
    {
        $entity     = $this->getEntityMock('Pim\Bundle\BatchBundle\Entity\Job');
        $event      = $this->getEventMock($entity);
        $violations = $this->getViolationsMock(100);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($entity, array('Default', 'Configuration'))
            ->will($this->returnValue($violations));

        $entity->expects($this->once())
            ->method('setStatus')
            ->with(Job::STATUS_DRAFT);

        $this->listener->prePersist($event);
    }

    private function getValidatorMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Validator\Validator')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getEventMock($entity)
    {
        $event = $this
            ->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        return $event;
    }

    private function getEntityMock($classname)
    {
        return $this
            ->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getViolationsMock($countViolations)
    {
        $violations = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolationList')
            ->disableOriginalConstructor()
            ->getMock();

        $violations->expects($this->any())
            ->method('count')
            ->will($this->returnValue($countViolations));

        return $violations;
    }
}
