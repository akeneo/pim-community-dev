<?php

namespace Oro\Bundle\NavigationBundle\Tests\Entity;

use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;

class NavigationHistoryItemTest extends \PHPUnit_Framework_TestCase
{
    public function testNavigationHistoryItemEntity()
    {
        $user = new User();
        $user->setEmail('some@email.com');

        $values = array(
            'title' => 'Some Title',
            'url'   => 'Some Url',
            'user'  => $user,
        );

        $item = new NavigationHistoryItem($values);
        $this->assertEquals($values['title'], $item->getTitle());
        $this->assertEquals($values['url'], $item->getUrl());
        $this->assertEquals($values['user'], $item->getUser());

        $dateTime = new \DateTime();
        $item->setVisitedAt($dateTime);
        $this->assertEquals($dateTime, $item->getVisitedAt());

        $visitCount = rand(0, 100);
        $item->setVisitCount($visitCount);
        $this->assertEquals($visitCount, $item->getVisitCount());

        $this->assertEquals(null, $item->getId());
    }

    public function testDoPrePersist()
    {
        $item = new NavigationHistoryItem();
        $item->doPrePersist();

        $this->assertInstanceOf('DateTime', $item->getVisitedAt());
        $this->assertEquals($item->getVisitCount(), 0);
    }

    public function testDoUpdate()
    {
        $item = new NavigationHistoryItem();
        $oldVisitedAt = $item->getVisitedAt();
        $oldVisitCount = $item->getVisitCount();

        $item->doUpdate();

        $this->assertInstanceOf('DateTime', $item->getVisitedAt());
        $this->assertNotEquals($oldVisitedAt, $item->getVisitedAt());
        $this->assertNotEquals($oldVisitCount, $item->getVisitCount());
        $this->assertEquals($oldVisitCount + 1, $item->getVisitCount());
    }
}
