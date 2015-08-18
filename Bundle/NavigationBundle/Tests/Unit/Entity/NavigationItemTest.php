<?php

namespace Oro\Bundle\NavigationBundle\Tests\Entity;

use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\NavigationBundle\Entity\NavigationItem;

class NavigationItemTest extends \PHPUnit_Framework_TestCase
{
    public function testNavigationItemEntity()
    {
        $user = new User();
        $user->setEmail('some@email.com');

        $values = array(
            'title' => 'Some Title',
            'url' => 'Some Url',
            'position' => 'Some position',
            'user' => $user
        );

        $item = new NavigationItem($values);
        $item->setType('test');
        $this->assertEquals($values['title'], $item->getTitle());
        $this->assertEquals($values['url'], $item->getUrl());
        $this->assertEquals($values['position'], $item->getPosition());
        $this->assertEquals($values['user'], $item->getUser());
        $this->assertEquals('test', $item->getType());

        $dateTime = new \DateTime();
        $item->setUpdatedAt($dateTime);
        $this->assertEquals($dateTime, $item->getUpdatedAt());

        $dateTime = new \DateTime();
        $item->setCreatedAt($dateTime);
        $this->assertEquals($dateTime, $item->getCreatedAt());
    }

    public function testDoPrePersist()
    {
        $item = new NavigationItem();
        $item->doPrePersist();

        $this->assertInstanceOf('DateTime', $item->getCreatedAt());
        $this->assertInstanceOf('DateTime', $item->getUpdatedAt());
        $this->assertEquals($item->getCreatedAt(), $item->getUpdatedAt());
    }

    public function testDoPreUpdate()
    {
        $item = new NavigationItem();
        $item->doPreUpdate();

        $this->assertInstanceOf('DateTime', $item->getUpdatedAt());
    }
}
