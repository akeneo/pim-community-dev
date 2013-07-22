<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Entity;

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
        $this->assertNull($this->entity->getTemplate());
        $this->entity->setTemplate('testTemplate');
        $this->assertEquals('testTemplate', $this->entity->getTemplate());
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
}
