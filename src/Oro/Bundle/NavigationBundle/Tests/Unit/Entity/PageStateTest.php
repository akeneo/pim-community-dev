<?php

namespace Oro\Bundle\NavigationBundle\Tests\Entity;

use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\NavigationBundle\Entity\PageState;

class PageStateTest extends \PHPUnit_Framework_TestCase
{
    public function testUser()
    {
        $item = new PageState();
        $user = new User();

        $this->assertNull($item->getId());
        $this->assertNull($item->getUser());

        $item->setUser($user);

        $this->assertEquals($user, $item->getUser());
    }

    public function testDateTime()
    {
        $item     = new PageState();
        $dateTime = new \DateTime();

        $item->setUpdatedAt($dateTime);
        $item->setCreatedAt($dateTime);

        $this->assertEquals($dateTime, $item->getUpdatedAt());
        $this->assertEquals($dateTime, $item->getCreatedAt());
    }

    public function testPageId()
    {
        $item   = new PageState();
        $pageId = 'SomeId';

        $item->setPageId($pageId);

        $this->assertEquals($pageId, $item->getPageId());
        $this->assertEquals(PageState::generateHash($pageId), $item->getPageHash());
    }

    public function testData()
    {
        $item = new PageState();
        $data = array(
            array('key' => 'val', 'key2' => 'val2'),
        );

        $item->setData($data);

        $this->assertEquals($data, $item->getData());
    }

    public function testDoPrePersist()
    {
        $item = new PageState();

        $item->doPrePersist();

        $this->assertInstanceOf('DateTime', $item->getCreatedAt());
        $this->assertInstanceOf('DateTime', $item->getUpdatedAt());
        $this->assertEquals($item->getCreatedAt(), $item->getUpdatedAt());
    }

    public function testDoPreUpdate()
    {
        $item = new PageState();

        $item->doPreUpdate();

        $this->assertInstanceOf('DateTime', $item->getUpdatedAt());
    }
}
