<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Menu;

use Oro\Bundle\NavigationBundle\Menu\NavigationHistoryBuilder;

class NavigationHistoryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var NavigationHistoryBuilder
     */
    protected $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manipulator;

    /**
     * @var \Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = $this->getMock('Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory');

        $this->builder = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Menu\NavigationHistoryBuilder')
            ->setConstructorArgs(array($this->securityContext, $this->em, $this->factory))
            ->setMethods(array('getMenuManipulator'))
            ->getMock();

        $this->manipulator = $this->getMock('Knp\Menu\Util\MenuManipulator');
        $this->builder->expects($this->any())->method('getMenuManipulator')
            ->will($this->returnValue($this->manipulator));
    }

    public function testBuild()
    {
        $type = 'history';
        $userId = 1;

        $user = $this->getMockBuilder('stdClass')
            ->setMethods(array('getId'))
            ->getMock();
        $user->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($userId));

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface');
        $this->factory->expects($this->once())
            ->method('createItem')
            ->with($type, array())
            ->will($this->returnValue($item));

        $repository = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Entity\Repository\HistoryItemRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $items = array(
            array('id' => 1, 'title' => 'test1', 'url' => '/'),
            array('id' => 2, 'title' => 'test2', 'url' => '/home'),
        );

        $repository->expects($this->once())
            ->method('getNavigationItems')
            ->with($userId, $type)
            ->will($this->returnValue($items));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(get_class($item))
            ->will($this->returnValue($repository));

        $menu = $this->getMockBuilder('Knp\Menu\MenuItem')->disableOriginalConstructor()->getMock();

        $childMock = $this->getMock('Knp\Menu\ItemInterface');
        $childMock2 = clone $childMock;
        $children = array($childMock, $childMock2);

        $matcher = $this->getMock('\Knp\Menu\Matcher\Matcher');
        $matcher->expects($this->once())
            ->method('isCurrent')
            ->will($this->returnValue(true));

        $this->builder->setMatcher($matcher);

        $menu->expects($this->exactly(2))
            ->method('addChild');
        $menu->expects($this->once())
            ->method('setExtra')
            ->with('type', $type);
        $menu->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue($children));
        $menu->expects($this->once())
            ->method('removeChild');

        $n = rand(1, 10);

        $configMock = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\UserConfigManager')
                        ->disableOriginalConstructor()
                        ->getMock();

        $configMock->expects($this->once())
                        ->method('get')
                        ->with($this->equalTo('oro_navigation.maxItems'))
                        ->will($this->returnValue($n));

        $this->manipulator->expects($this->once())
            ->method('slice')
            ->with($menu, 0, $n);

        $this->builder->setOptions($configMock);
        $this->builder->build($menu, array(), $type);
    }
}
