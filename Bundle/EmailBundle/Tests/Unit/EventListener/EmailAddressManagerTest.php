<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\EventListener;

use Oro\Bundle\EmailBundle\EventListener\EmailAddressManager;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;

class EmailAddressManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var EmailAddressManager */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $emailOwner;

    /** @var string */
    private $emailOwnerClass;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $loadEventArgs;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $flushEventArgs;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $metadata;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $uow;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $em;

    private function initLoadClassMetadata()
    {
        $this->emailOwner = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');

        $this->emailOwnerClass = 'Oro\Bundle\SomeBundle\Entity\SomeEntity';

        $provider = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider->expects($this->once())
            ->method('getEmailOwnerClass')
            ->will($this->returnValue($this->emailOwnerClass));

        $this->manager = new EmailAddressManager();
        $this->manager->addProvider($provider);

        $this->loadEventArgs = $this->getMockBuilder('Doctrine\ORM\Event\LoadClassMetadataEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testHandleLoadClassMetadataNotEmailAddress()
    {
        $this->initLoadClassMetadata();

        $this->loadEventArgs->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->metadata));
        $this->metadata->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('SomeEntity'));
        $this->metadata->expects($this->never())
            ->method('mapManyToOne');

        $this->manager->handleLoadClassMetadata($this->loadEventArgs);
    }

    public function testHandleLoadClassMetadata()
    {
        $this->initLoadClassMetadata();

        $this->loadEventArgs->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->metadata));
        $this->metadata->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Oro\Bundle\EmailBundle\Entity\EmailAddress'));
        $this->metadata->expects($this->once())
            ->method('mapManyToOne')
            ->with(
                $this->equalTo(
                    array(
                        'targetEntity' => $this->emailOwnerClass,
                        'fieldName' => '_owner1',
                        'joinColumns' => array(
                            array(
                                'name' => 'owner_someentity_id',
                                'referencedColumnName' => 'id'
                            )
                        )
                    )
                )
            );

        $this->manager->handleLoadClassMetadata($this->loadEventArgs);
    }

    private function initOnFlush()
    {
        $this->uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->flushEventArgs = $this->getMockBuilder('Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider handleOnFlushProvider
     */
    public function testHandleOnFlush(
        $handleInsertionsOrUpdatesReturnValue,
        $handleDeletionsReturnValue,
        $expectComputeChangeSets
    ) {
        $this->initOnFlush();

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->flushEventArgs->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em));

        $manager = $this->getMockBuilder('Oro\Bundle\EmailBundle\EventListener\EmailAddressManager')
            ->setMethods(array('handleInsertionsOrUpdates', 'handleDeletions'))
            ->getMock();

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue(array('ScheduledEntityInsertions')));

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array('ScheduledEntityUpdates')));

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue(array('ScheduledEntityDeletions')));

        $manager->expects($this->exactly(2))
            ->method('handleInsertionsOrUpdates')
            ->with(
                $this->logicalOr(
                    $this->equalTo(array('ScheduledEntityInsertions')),
                    $this->equalTo(array('ScheduledEntityUpdates'))
                ),
                $this->identicalTo($this->em),
                $this->identicalTo($this->uow)
            )
            ->will($this->returnValue($handleInsertionsOrUpdatesReturnValue));

        $manager->expects($this->once())
            ->method('handleDeletions')
            ->with(
                $this->equalTo(array('ScheduledEntityDeletions')),
                $this->identicalTo($this->em)
            )
            ->will($this->returnValue($handleDeletionsReturnValue));

        $this->uow->expects($expectComputeChangeSets ? $this->once() : $this->never())
            ->method('computeChangeSets');

        $manager->handleOnFlush($this->flushEventArgs);
    }

    public function handleOnFlushProvider()
    {
        return array(
            'no changes' => array(false, false, false),
            'has updates' => array(true, false, true),
            'has deletion' => array(false, true, true),
            'has updates and deletion' => array(true, true, true),
        );
    }

    /**
     * @dataProvider handleInsertionsOrUpdatesProvider
     */
    public function testHandleInsertionsOrUpdates(
        $entity,
        $processInsertionOrUpdateEntityCall,
        $processInsertionOrUpdateEntityReturnValue,
        $returnValue
    ) {
        $this->initOnFlush();

        $manager = $this->getMockBuilder('Oro\Bundle\EmailBundle\EventListener\EmailAddressManager')
            ->setMethods(array('processInsertionOrUpdateEntity'))
            ->getMock();

        if ($processInsertionOrUpdateEntityCall) {
            if ($entity instanceof EmailOwnerInterface) {
                $args = array('SomeField', $entity, $entity, $this->em, $this->uow);
            } elseif ($entity instanceof EmailInterface) {
                $args = array('SomeField', $entity, $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface'), $this->em, $this->uow);
            } else {
                $this->fail('Unexpected entity type');
                return;
            }

            $manager->expects($this->once())
                ->method('processInsertionOrUpdateEntity')
                ->with($this->equalTo($args[0]), $this->equalTo($args[1]), $this->equalTo($args[2]), $this->equalTo($args[3]), $this->equalTo($args[4]))
                ->will($this->returnValue($processInsertionOrUpdateEntityReturnValue));
        } else {
            $manager->expects($this->never())
                ->method('processInsertionOrUpdateEntity');
        }

        $result = $this->callProtectedMethod(
            $manager,
            'handleInsertionsOrUpdates',
            array($entity === null ? array() :  array($entity), $this->em, $this->uow)
        );
        $this->assertEquals($returnValue, $result);
    }

    public function handleInsertionsOrUpdatesProvider()
    {
        return array(
            'no items' => array(null, false, false, false),
            'not tracked item' => array(new \stdClass(), false, false, false),
            'EmailOwnerInterface nothing to change' =>
            array($this->handleInsertionsOrUpdatesPrepareMockForEmailOwnerInterface(), true, false, false),
            'EmailOwnerInterface' => array($this->handleInsertionsOrUpdatesPrepareMockForEmailOwnerInterface(), true, true, true),
            'EmailInterface nothing to change' =>
            array($this->handleInsertionsOrUpdatesPrepareMockForEmailInterface(), true, false, false),
            'EmailInterface' => array($this->handleInsertionsOrUpdatesPrepareMockForEmailInterface(), true, true, true),
        );
    }

    private function handleInsertionsOrUpdatesPrepareMockForEmailOwnerInterface()
    {
        $mock = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');
        $mock->expects($this->once())
            ->method('getPrimaryEmailField')
            ->will($this->returnValue('SomeField'));

        return $mock;
    }

    private function handleInsertionsOrUpdatesPrepareMockForEmailInterface()
    {
        $mock = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailInterface');
        $mock->expects($this->once())
            ->method('getEmailField')
            ->will($this->returnValue('SomeField'));
        $mock->expects($this->once())
            ->method('getEmailOwner')
            ->will($this->returnValue($this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface')));

        return $mock;
    }

    /**
     * @dataProvider handleDeletionsProvider
     */
    public function testHandleDeletions(
        $entity,
        $unbindEmailAddressCall,
        $unbindEmailAddressReturnValue,
        $returnValue
    ) {
        $this->initOnFlush();

        $manager = $this->getMockBuilder('Oro\Bundle\EmailBundle\EventListener\EmailAddressManager')
            ->setMethods(array('unbindEmailAddress'))
            ->getMock();

        if ($unbindEmailAddressCall) {
            if ($entity instanceof EmailOwnerInterface) {
                $args = array($this->em, $entity, null);
                $manager->expects($this->once())
                    ->method('unbindEmailAddress')
                    ->with($this->equalTo($args[0]), $this->equalTo($args[1]))
                    ->will($this->returnValue($unbindEmailAddressReturnValue));
            } elseif ($entity instanceof EmailInterface) {
                $args = array($this->em, $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface'), $entity);
                $manager->expects($this->once())
                    ->method('unbindEmailAddress')
                    ->with($this->equalTo($args[0]), $this->equalTo($args[1]), $this->equalTo($args[2]))
                    ->will($this->returnValue($unbindEmailAddressReturnValue));
            } else {
                $this->fail('Unexpected entity type');
                return;
            }
        } else {
            $manager->expects($this->never())
                ->method('unbindEmailAddress');
        }

        $result = $this->callProtectedMethod(
            $manager,
            'handleDeletions',
            array($entity === null ? array() :  array($entity), $this->em)
        );
        $this->assertEquals($returnValue, $result);
    }

    public function handleDeletionsProvider()
    {
        return array(
            'no items' => array(null, false, false, false),
            'not tracked item' => array(new \stdClass(), false, false, false),
            'EmailOwnerInterface nothing to change' =>
            array($this->handleDeletionsPrepareMockForEmailOwnerInterface(), true, false, false),
            'EmailOwnerInterface' => array($this->handleDeletionsPrepareMockForEmailOwnerInterface(), true, true, true),
            'EmailInterface nothing to change' =>
            array($this->handleDeletionsPrepareMockForEmailInterface(), true, false, false),
            'EmailInterface' => array($this->handleDeletionsPrepareMockForEmailInterface(), true, true, true),
        );
    }

    private function handleDeletionsPrepareMockForEmailOwnerInterface()
    {
        $mock = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');

        return $mock;
    }

    private function handleDeletionsPrepareMockForEmailInterface()
    {
        $mock = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailInterface');
        $mock->expects($this->once())
            ->method('getEmailOwner')
            ->will($this->returnValue($this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface')));

        return $mock;
    }

    private function callProtectedMethod($obj, $methodName, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
