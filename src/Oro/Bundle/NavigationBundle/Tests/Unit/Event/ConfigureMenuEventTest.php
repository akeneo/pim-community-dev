<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

class ConfigureMenuEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var ItemInterface
     */
    protected $menu;

    /**
     * @var ConfigureMenuEvent
     */
    protected $event;

    protected function setUp()
    {
        $this->factory = $this->getMockBuilder('Knp\Menu\FactoryInterface')
            ->getMock();
        $this->menu = $this->getMockBuilder('Knp\Menu\ItemInterface')
            ->getMock();
        $this->event = new ConfigureMenuEvent($this->factory, $this->menu);
    }

    public function testGetFactory()
    {
        $this->assertEquals($this->event->getFactory(), $this->factory);
    }

    public function testGetMenu()
    {
        $this->assertEquals($this->event->getMenu(), $this->menu);
    }

    public function testGetEventName()
    {
        $this->assertEquals('oro_menu.configure.test', ConfigureMenuEvent::getEventName('test'));
    }
}
