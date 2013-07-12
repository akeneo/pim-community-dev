<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Provider;

use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

class BuilderChainProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FactoryInterface
     */
    protected $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var BuilderChainProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->factory = $this->getMockBuilder('Knp\Menu\MenuFactory')
            ->getMock();
        $this->eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->getMock();
        $this->provider = new BuilderChainProvider($this->factory, $this->eventDispatcher);
    }

    public function testAddBuilder()
    {
        $builder = $this->getMenuBuilderMock();
        $this->provider->addBuilder($builder, 'builder1');
        $this->provider->addBuilder($builder, 'builder1');
        $this->provider->addBuilder($builder, 'builder2');
        $this->assertAttributeCount(2, 'builders', $this->provider);
        $expectedBuilders = array('builder1' => array($builder, $builder), 'builder2' => array($builder));
        $this->assertAttributeEquals($expectedBuilders, 'builders', $this->provider);
    }

    public function testHas()
    {
        $this->provider->addBuilder($this->getMenuBuilderMock(), 'test');
        $this->assertTrue($this->provider->has('test'));
        $this->assertFalse($this->provider->has('unknown'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Menu alias was not set.
     */
    public function testGetException()
    {
        $this->provider->get('');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Menu alias was not set.
     */
    public function testHasException()
    {
        $this->provider->has('');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Menu alias was not set.
     */
    public function testAddBuilderException()
    {
        $this->provider->addBuilder($this->getMenuBuilderMock(), '');
    }

    /**
     * @dataProvider aliasDataProvider
     * @param string $alias
     * @param string $menuName
     */
    public function testGet($alias, $menuName)
    {
        $options = array();

        $menu = $this->getMockBuilder('Knp\Menu\ItemInterface')
            ->getMock();

        $this->factory->expects($this->once())
            ->method('createItem')
            ->with($menuName)
            ->will($this->returnValue($menu));

        $builder = $this->getMenuBuilderMock();
        $builder->expects($this->once())
            ->method('build')
            ->with($menu, $options, $menuName);
        $this->provider->addBuilder($builder, $alias);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                'oro_menu.configure.' . $menuName,
                $this->isInstanceOf('Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent')
            );

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $this->provider->get($menuName, $options));
        $this->assertInstanceOf('Knp\Menu\ItemInterface', $this->provider->get($menuName, $options));

        $this->assertAttributeCount(1, 'menus', $this->provider);
    }

    public function testGetCached()
    {
        $options = array();

        $alias = 'test_menu';
        $items = array('name' => $alias);
        $menu = $this->getMockBuilder('Knp\Menu\ItemInterface')
            ->getMock();

        $cache = $this->getMockBuilder('Doctrine\Common\Cache\ArrayCache')
            ->getMock();

        $cache->expects($this->once())
            ->method('contains')
            ->with($alias)
            ->will($this->returnValue(true));

        $cache->expects($this->once())
            ->method('fetch')
            ->with($alias)
            ->will($this->returnValue($items));

        $this->factory->expects($this->once())
            ->method('createFromArray')
            ->with($items)
            ->will($this->returnValue($menu));

        $this->factory->expects($this->never())
            ->method('createItem');

        $builder = $this->getMenuBuilderMock();
        $builder->expects($this->never())
            ->method('build');

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->provider->addBuilder($builder, $alias);
        $this->provider->setCache($cache);

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $this->provider->get($alias, $options));
        $this->assertAttributeCount(1, 'menus', $this->provider);
    }

    /**
     * @return array
     */
    public function aliasDataProvider()
    {
        return array(
            'custom alias' => array('test', 'test'),
            'global' => array(BuilderChainProvider::COMMON_BUILDER_ALIAS, 'test')
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|BuilderInterface
     */
    protected function getMenuBuilderMock()
    {
        return $this->getMockBuilder('Oro\Bundle\NavigationBundle\Menu\BuilderInterface')
            ->getMock();
    }

    public function testSorting()
    {
        $menuName = 'test_menu';
        $options = array();

        $topMenu = $this->getMockBuilder('Knp\Menu\ItemInterface')
            ->getMock();

        $topMenu->expects($this->any())
            ->method('hasChildren')
            ->will($this->returnValue(true));

        $topMenu->expects($this->any())
            ->method('getDisplayChildren')
            ->will($this->returnValue(true));

        $menu = $this->getMockBuilder('Knp\Menu\ItemInterface')
            ->getMock();

        $menu->expects($this->any())
            ->method('hasChildren')
            ->will($this->returnValue(true));

        $menu->expects($this->any())
            ->method('getDisplayChildren')
            ->will($this->returnValue(true));

        $childOne = $this->getChildItem('child1', 5);
        $childTwo = $this->getChildItem('child2', 10);
        $childThree = $this->getChildItem('child3');
        $childFour = $this->getChildItem('child4');

        $menu->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue(array($childThree, $childFour, $childTwo, $childOne)));

        $topMenu->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue(array($menu)));

        $this->factory->expects($this->once())
            ->method('createItem')
            ->with($menuName)
            ->will($this->returnValue($topMenu));

        $menu->expects($this->once())
            ->method('reorderChildren')
            ->with(array('child1', 'child2', 'child3', 'child4'));

        $newMenu = $this->provider->get($menuName, $options);
        $this->assertInstanceOf('Knp\Menu\ItemInterface', $newMenu);
    }

    /**
     * @param  string                                   $name
     * @param  null|int                                 $position
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getChildItem($name, $position = null)
    {
        $child = $this->getMockBuilder('Knp\Menu\ItemInterface')
            ->getMock();
        $child->expects($this->once())
            ->method('getExtra')
            ->with('position')
            ->will($this->returnValue($position));
        $child->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));

        return $child;
    }
}
