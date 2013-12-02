<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;

class EmailNotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailNotification
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new EmailNotification();

        // get id should return null cause this entity was not loaded from DB
        $this->assertNull($this->entity->getId());
    }

    public function tearDown()
    {
        unset($this->entity);
    }

    public function testGetterSetterForEntityName()
    {
        $this->assertNull($this->entity->getEntityName());
        $this->entity->setEntityName('testName');
        $this->assertEquals('testName', $this->entity->getEntityName());
    }

    public function testGetterSetterForTemplate()
    {
        $emailTemplate = $this->getMock('Oro\Bundle\EmailBundle\Entity\EmailTemplate');
        $this->assertNull($this->entity->getTemplate());
        $this->entity->setTemplate($emailTemplate);
        $this->assertEquals($emailTemplate, $this->entity->getTemplate());
    }

    public function testGetterSetterForEvent()
    {
        $this->assertNull($this->entity->getEvent());

        $event = $this->getMock('Oro\Bundle\NotificationBundle\Entity\Event', array(), array('test.name'));
        $this->entity->setEvent($event);
        $this->assertEquals($event, $this->entity->getEvent());
    }

    public function testGetterSetterForRecipients()
    {
        $this->assertNull($this->entity->getRecipientList());

        $list = $this->getMock('Oro\Bundle\NotificationBundle\Entity\RecipientList');
        $this->entity->setRecipientList($list);
        $this->assertEquals($list, $this->entity->getRecipientList());
    }

    public function testGetUsersRecipientsList()
    {
        $this->assertTrue($this->entity->getRecipientUsersList()->isEmpty());

        $userMock1 = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $userMock2 = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $collection = new ArrayCollection(array($userMock1, $userMock2));

        $list = $this->getMock('Oro\Bundle\NotificationBundle\Entity\RecipientList');
        $list->expects($this->once())->method('getUsers')->will($this->returnValue($collection));
        $this->entity->setRecipientList($list);

        $actual = $this->entity->getRecipientUsersList();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($collection, $actual);
    }

    public function testGetGroupsRecipientsList()
    {
        $this->assertTrue($this->entity->getRecipientGroupsList()->isEmpty());

        $groupMock1 = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');
        $groupMock2 = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');

        $collection = new ArrayCollection(array($groupMock1, $groupMock2));

        $list = $this->getMock('Oro\Bundle\NotificationBundle\Entity\RecipientList');
        $list->expects($this->once())->method('getGroups')->will($this->returnValue($collection));
        $this->entity->setRecipientList($list);

        $actual = $this->entity->getRecipientGroupsList();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($collection, $actual);
    }
}
