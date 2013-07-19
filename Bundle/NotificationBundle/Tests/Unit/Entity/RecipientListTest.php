<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Entity;

use Oro\Bundle\NotificationBundle\Entity\RecipientList;

class RecipientListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RecipientList
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new RecipientList();

        // get id should return null cause this entity was not loaded from DB
        $this->assertNull($this->entity->getId());

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->entity->getUsers());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->entity->getGroups());
    }

    public function testSetterGetterForUsers()
    {
        // test adding through array collection interface
        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $this->entity->getUsers()->add($user);

        $this->assertContains($user, $this->entity->getUsers());

        // clear collection
        $this->entity->getUsers()->clear();
        $this->assertTrue($this->entity->getUsers()->isEmpty());

        // test setter
        $this->entity->setUsers(array($user));
        $this->assertContains($user, $this->entity->getUsers());
    }

    public function testSetterGetterForGroups()
    {
        // test adding through array collection interface
        $group = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');
        $this->entity->getGroups()->add($group);

        $this->assertContains($group, $this->entity->getGroups());

        // clear collection
        $this->entity->getGroups()->clear();
        $this->assertTrue($this->entity->getGroups()->isEmpty());

        // test setter
        $this->entity->addGroup($group);
        $this->assertContains($group, $this->entity->getGroups());


        // remove group
        $this->entity->removeGroup($group);
        $this->assertTrue($this->entity->getGroups()->isEmpty());
    }

    public function testSetterGetterForEmail()
    {
        $this->assertNull($this->entity->getEmail());

        $this->entity->setEmail('test');
        $this->assertEquals('test', $this->entity->getEmail());
    }

    public function testSetterGetterForOwner()
    {
        $this->assertNull($this->entity->getOwner());

        $this->entity->setOwner(true);
        $this->assertEquals(true, $this->entity->getOwner());
    }

    public function testToString()
    {
        $group = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');

        // test when owner filled
        $this->entity->setOwner(true);
        $this->assertInternalType('string', $this->entity->__toString());
        $this->assertNotEmpty($this->entity->__toString());
        // clear owner
        $this->entity->setOwner(null);

        // test when email filled
        $this->entity->setEmail('test email');
        $this->assertInternalType('string', $this->entity->__toString());
        $this->assertNotEmpty($this->entity->__toString());
        // clear email
        $this->entity->setEmail(null);

        // test when users filled
        $this->entity->setUsers(array('testUser'));
        $this->assertInternalType('string', $this->entity->__toString());
        $this->assertNotEmpty($this->entity->__toString());
        // clear email
        $this->entity->setUsers(array());

        // test when groups filled
        $this->entity->addGroup($group);
        $this->assertInternalType('string', $this->entity->__toString());
        $this->assertNotEmpty($this->entity->__toString());
        // clear email
        $this->entity->getGroups()->clear();

        // should be empty if nothing filled
        $this->assertEmpty($this->entity->__toString());
    }
}
