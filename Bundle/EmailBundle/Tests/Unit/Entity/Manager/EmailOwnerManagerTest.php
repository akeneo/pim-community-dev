<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailOwnerManager;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\EmailBundle\Tests\Unit\ReflectionUtil;

class EmailOwnerManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $flushEventArgs;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $uow;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $em;

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

    private function getEmailOwnerProviderStorageMock()
    {
        $provider = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $provider->expects($this->any())
            ->method('getProviders')
            ->will($this->returnValue('SomeEntity'));
        $storage = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderStorage');
        $storage->expects($this->any())
            ->method('getProviders')
            ->will($this->returnValue(array($provider)));

        return $storage;
    }

    /**
     * @dataProvider handleOnFlushProvider
     */
    public function testHandleOnFlush(
        $handleInsertionsOrUpdatesReturnValue,
        $handleDeletionsReturnValue
    ) {
        $this->initOnFlush();

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->flushEventArgs->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em));

        $manager = $this->createEmailOwnerManagerMockBuilder()
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

        $manager->handleOnFlush($this->flushEventArgs);
    }

    public function handleOnFlushProvider()
    {
        return array(
            'no changes' => array(false, false),
            'has updates' => array(true, false),
            'has deletion' => array(false, true),
            'has updates and deletion' => array(true, true),
        );
    }

    /**
     * @dataProvider handleInsertionsOrUpdatesProvider
     */
    public function testHandleInsertionsOrUpdates(
        $entity,
        $processInsertionOrUpdateEntityCall,
        $processInsertionOrUpdateEntityReturnValue
    ) {
        $this->initOnFlush();

        $manager = $this->createEmailOwnerManagerMockBuilder()
            ->setMethods(array('processInsertionOrUpdateEntity'))
            ->getMock();

        if ($processInsertionOrUpdateEntityCall) {
            if ($entity instanceof EmailOwnerInterface) {
                $args = array('SomeField', $entity, $entity, $this->em, $this->uow);
            } elseif ($entity instanceof EmailInterface) {
                $args = array(
                    'SomeField',
                    $entity,
                    $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface'),
                    $this->em,
                    $this->uow
                );
            } else {
                $this->fail('Unexpected entity type');

                return;
            }

            $manager->expects($this->once())
                ->method('processInsertionOrUpdateEntity')
                ->with(
                    $this->equalTo($args[0]),
                    $this->equalTo($args[1]),
                    $this->equalTo($args[2]),
                    $this->equalTo($args[3]),
                    $this->equalTo($args[4])
                )
                ->will($this->returnValue($processInsertionOrUpdateEntityReturnValue));
        } else {
            $manager->expects($this->never())
                ->method('processInsertionOrUpdateEntity');
        }

        ReflectionUtil::callProtectedMethod(
            $manager,
            'handleInsertionsOrUpdates',
            array($entity === null ? array() : array($entity), $this->em, $this->uow)
        );
    }

    public function handleInsertionsOrUpdatesProvider()
    {
        return array(
            'no items' => array(null, false, false),
            'not tracked item' => array(new \stdClass(), false, false),
            'EmailOwnerInterface nothing to change' =>
            array($this->handleInsertionsOrUpdatesPrepareMockForEmailOwnerInterface(), true, false),
            'EmailOwnerInterface' => array(
                $this->handleInsertionsOrUpdatesPrepareMockForEmailOwnerInterface(),
                true,
                true,
            ),
            'EmailInterface nothing to change' =>
            array($this->handleInsertionsOrUpdatesPrepareMockForEmailInterface(), true, false),
            'EmailInterface' => array($this->handleInsertionsOrUpdatesPrepareMockForEmailInterface(), true, true),
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
        $unbindEmailAddressReturnValue
    ) {
        $this->initOnFlush();

        $manager = $this->createEmailOwnerManagerMockBuilder()
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

        ReflectionUtil::callProtectedMethod(
            $manager,
            'handleDeletions',
            array($entity === null ? array() : array($entity), $this->em)
        );
    }

    public function handleDeletionsProvider()
    {
        return array(
            'no items' => array(null, false, false),
            'not tracked item' => array(new \stdClass(), false, false),
            'EmailOwnerInterface nothing to change' =>
            array($this->handleDeletionsPrepareMockForEmailOwnerInterface(), true, false),
            'EmailOwnerInterface' => array($this->handleDeletionsPrepareMockForEmailOwnerInterface(), true, true),
            'EmailInterface nothing to change' =>
            array($this->handleDeletionsPrepareMockForEmailInterface(), true, false),
            'EmailInterface' => array($this->handleDeletionsPrepareMockForEmailInterface(), true, true),
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

    public function testProcessInsertionOrUpdateEntityNoEmailField()
    {
        $this->initOnFlush();

        $manager = $this->createEmailOwnerManagerMockBuilder()
            ->setMethods(array('bindEmailAddress'))
            ->getMock();

        $this->uow->expects($this->never())
            ->method('getEntityChangeSet');

        $manager->expects($this->never())
            ->method('bindEmailAddress');

        $owner = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');

        ReflectionUtil::callProtectedMethod(
            $manager,
            'processInsertionOrUpdateEntity',
            array(null, null, $owner, $this->em, $this->uow)
        );
    }

    public function testProcessInsertionOrUpdateEntityNoEmailRelatedChanges()
    {
        $this->initOnFlush();

        $manager = $this->createEmailOwnerManagerMockBuilder()
            ->setMethods(array('bindEmailAddress'))
            ->getMock();

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->will($this->returnValue(array('SomeField' => array('val1', 'val2'))));

        $manager->expects($this->never())
            ->method('bindEmailAddress');

        $owner = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');

        ReflectionUtil::callProtectedMethod(
            $manager,
            'processInsertionOrUpdateEntity',
            array('testEmailField', null, $owner, $this->em, $this->uow)
        );
    }

    public function testProcessInsertionOrUpdateEntityEmailValueNotChanged()
    {
        $this->initOnFlush();

        $manager = $this->createEmailOwnerManagerMockBuilder()
            ->setMethods(array('bindEmailAddress'))
            ->getMock();

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->will($this->returnValue(array('testEmailField' => array('val1', 'val1'))));

        $manager->expects($this->never())
            ->method('bindEmailAddress');

        $owner = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');

        ReflectionUtil::callProtectedMethod(
            $manager,
            'processInsertionOrUpdateEntity',
            array('testEmailField', null, $owner, $this->em, $this->uow)
        );
    }

    public function testProcessInsertionOrUpdateEntityEmailValueChanged()
    {
        $this->initOnFlush();

        $manager = $this->createEmailOwnerManagerMockBuilder()
            ->setMethods(array('bindEmailAddress'))
            ->getMock();

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->will($this->returnValue(array('testEmailField' => array('val1', 'val2'))));

        $manager->expects($this->once())
            ->method('bindEmailAddress')
            ->will($this->returnValue(true));

        $owner = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');

        ReflectionUtil::callProtectedMethod(
            $manager,
            'processInsertionOrUpdateEntity',
            array('testEmailField', null, $owner, $this->em, $this->uow)
        );
    }

    public function testCreateEmailAddress()
    {
        $owner = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface');
        $addrManager = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager')
            ->disableOriginalConstructor()
            ->getMock();
        $addr = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailAddress');

        $addrManager->expects($this->once())
            ->method('newEmailAddress')
            ->will($this->returnValue($addr));

        $addr->expects($this->once())
            ->method('setEmail')
            ->with($this->equalTo('test@example.com'))
            ->will($this->returnValue($addr));
        $addr->expects($this->once())
            ->method('setOwner')
            ->with($this->identicalTo($owner))
            ->will($this->returnValue($addr));

        $manager = new EmailOwnerManager(
            $this->getEmailOwnerProviderStorageMock(),
            $addrManager
        );

        ReflectionUtil::callProtectedMethod(
            $manager,
            'createEmailAddress',
            array('test@example.com', $owner)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     */
    private function createEmailOwnerManagerMockBuilder()
    {
        return $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Manager\EmailOwnerManager')
            ->setConstructorArgs(
                array(
                    $this->getEmailOwnerProviderStorageMock(),
                    new EmailAddressManager('SomeNamespace', 'ProxyFor%s')
                )
            );
    }
}
