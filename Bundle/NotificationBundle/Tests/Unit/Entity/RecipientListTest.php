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

    public function tearDown()
    {
        unset($this->entity);
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
        $this->entity->addUser($user);
        $this->assertContains($user, $this->entity->getUsers());


        // remove group
        $this->entity->removeUser($user);
        $this->assertTrue($this->entity->getUsers()->isEmpty());
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
        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

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
        $this->entity->addUser($user);
        $this->assertInternalType('string', $this->entity->__toString());
        $this->assertNotEmpty($this->entity->__toString());
        // clear users
        $this->entity->getUsers()->clear();

        // test when groups filled
        $this->entity->addGroup($group);
        $this->assertInternalType('string', $this->entity->__toString());
        $this->assertNotEmpty($this->entity->__toString());
        // clear groups
        $this->entity->getGroups()->clear();

        // should be empty if nothing filled
        $this->assertEmpty($this->entity->__toString());
    }

    public function testNotValidData()
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('getPropertyPath')
            ->will($this->returnValue('testPath'));
        $context->expects($this->once())
            ->method('addViolationAt');

        $this->entity->isValid($context);
    }

    public function testValidData()
    {
        $group = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');
        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->never())
            ->method('getPropertyPath');
        $context->expects($this->never())
            ->method('addViolationAt');

        //only users
        $this->entity->addUser($user);
        $this->entity->isValid($context);
        // clear users
        $this->entity->getUsers()->clear();

        //only groups
        $this->entity->addGroup($group);
        $this->entity->isValid($context);
        // clear groups
        $this->entity->getGroups()->clear();

        // only email
        $this->entity->setEmail('test Email');
        $this->entity->isValid($context);
        $this->entity->setEmail(null);

        // only owner
        $this->entity->setOwner(true);
        $this->entity->isValid($context);
        $this->entity->setEmail(null);
    }
}
