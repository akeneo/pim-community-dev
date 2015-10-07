<?php

namespace Oro\Bundle\NavigationBundle\Tests\Entity;

use Oro\Bundle\NavigationBundle\Entity\PinbarTab;

class PinbarTabTest extends \PHPUnit_Framework_TestCase
{
    public function testSetMaximizedNotEmpty()
    {
        $item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationItem');

        $pinbarTab = new PinbarTab();
        $pinbarTab->setItem($item);
        $pinbarTab->setMaximized('2022-02-02 22:22:22');

        $this->assertInstanceOf('DateTime', $pinbarTab->getMaximized());
    }

    public function testSetMaximizedEmpty()
    {
        $item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationItem');

        $pinbarTab = new PinbarTab();
        $pinbarTab->setItem($item);
        $pinbarTab->setMaximized('');

        $this->assertNull($pinbarTab->getMaximized());
    }

    public function testSetGet()
    {
        $item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationItem');

        $pinbarTab = new PinbarTab();
        $pinbarTab->setItem($item);

        $this->assertSame($item, $pinbarTab->getItem());
    }

    public function testDoPrePersist()
    {
        $item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationItem');

        $pinbarTab = new PinbarTab();
        $pinbarTab->setItem($item);
        $pinbarTab->doPrePersist();

        $this->assertNull($pinbarTab->getMaximized());
    }

    public function testSetValues()
    {
        $values = array('maximized' => '2022-02-02 22:22:22', 'url' => '/');
        $item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationItem');
        $item->expects($this->once())
            ->method('setValues')
            ->with($values);
        $pinbarTab = new PinbarTab();
        $pinbarTab->setItem($item);
        $pinbarTab->setValues($values);
        $this->assertInstanceOf('DateTime', $pinbarTab->getMaximized());
    }

    public function testGetUserNoItem()
    {
        $pinbarTab = new PinbarTab();
        $this->assertNull($pinbarTab->getUser());
    }

    public function testGetUser()
    {
        $user = $this->getMock('stdClass');
        $item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationItem');
        $item->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $pinbarTab = new PinbarTab();
        $pinbarTab->setItem($item);
        $this->assertSame($user, $pinbarTab->getUser());
    }
}
