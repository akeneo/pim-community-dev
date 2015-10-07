<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Menu;

use Oro\Bundle\NavigationBundle\Menu\BreadcrumbManager;

use Knp\Menu\MenuItem;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider;

class BreadcrumbManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BreadcrumbManager
     */
    protected $manager;

    protected $matcher;

    protected $router;

    protected $provider;

    protected $factory;

    protected function setUp()
    {
        $this->matcher = $this->getMockBuilder('Knp\Menu\Matcher\Matcher')
            ->disableOriginalConstructor()
            ->getMock();

        $this->router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = $this->getMockBuilder('Knp\Menu\MenuFactory')
            ->setMethods(array('getRouteInfo', 'processRoute'))
            ->getMock();

        $this->manager = new BreadcrumbManager($this->provider, $this->matcher, $this->router);
    }

    public function testGetBreadcrumbs()
    {
        $item = new MenuItem('test', $this->factory);
        $subItem = new MenuItem('sub_item_test', $this->factory);
        $subItem->setCurrent(true);
        $item->addChild($subItem);

        $this->provider->expects($this->once())
            ->method('get')
            ->with(
                'test',
                array('check_access' => false)
            )
            ->will($this->returnValue($item));

        $this->matcher->expects($this->any())
            ->method('isCurrent')
            ->with($subItem)
            ->will($this->returnValue(true));


        $breadcrumbs = $this->manager->getBreadcrumbs('test', false);
        $this->assertEquals('sub_item_test', $breadcrumbs[0]['label']);
    }

    public function testGetBreadcrumbsWOItem()
    {
        $item = new MenuItem('test', $this->factory);

        $this->provider->expects($this->once())
            ->method('get')
            ->will($this->returnValue($item));
        $this->assertNull($this->manager->getBreadcrumbs('nullable'));
    }

    public function testGetBreadcrumbLabels()
    {
        $item = new MenuItem('test', $this->factory);
        $item->setExtra('routes', array(
            'another_route',
            '/another_route/',
            'another*route',
            'test_route',
        ));
        $item1 = new MenuItem('test1', $this->factory);
        $item2 = new MenuItem('sub_item', $this->factory);
        $item1->addChild($item2);
        $item1->setExtra('routes', array());
        $item2->addChild($item);


        $this->provider->expects($this->once())
            ->method('get')
            ->will($this->returnValue($item1));

        $this->assertEquals(
            array('test', 'sub_item', 'test1'),
            $this->manager->getBreadcrumbLabels('test_menu', 'test_route')
        );
    }

    public function testGetMenu()
    {
        $item = new MenuItem('testItem', $this->factory);
        $subItem = new MenuItem('subItem', $this->factory);
        $item->addChild($subItem);
        $this->provider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($item));

        $resultMenu = $this->manager->getMenu('test', array('subItem'));
        $this->assertEquals($subItem, $resultMenu);

        $this->setExpectedException('InvalidArgumentException');
        $this->manager->getMenu('test', array('bad_item'));
    }

    public function testGetCurrentMenuItem()
    {
        $item = new MenuItem('testItem', $this->factory);
        $goodItem = new MenuItem('goodItem', $this->factory);
        $subItem = new MenuItem('subItem', $this->factory);
        $goodItem->addChild($subItem);

        $params = array(
            'testItem' => false,
            'goodItem' => false,
            'subItem' => true,
        );

        $this->matcher->expects($this->any())
            ->method('isCurrent')
            ->with(
                $this->logicalOr(
                    $this->equalTo($item),
                    $this->equalTo($goodItem),
                    $this->equalTo($subItem)
                )
            )
            ->will(
                $this->returnCallback(
                    function ($param) use (&$params) {
                        return $params[$param->getLabel()];
                    }
                )
            );

        $this->assertEquals($subItem, $this->manager->getCurrentMenuItem(array($item, $goodItem)));
    }
}
