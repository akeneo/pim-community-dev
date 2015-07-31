<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Menu;

use Oro\Bundle\NavigationBundle\Menu\NavigationItemBuilder;

class NavigationItemBuilderBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var NavigationItemBuilder
     */
    protected $builder;

    /**
     * @var \Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMock('Symfony\Component\Security\Core\TokenStorageInterface');
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = $this->getMock('Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory');
        $this->builder = new NavigationItemBuilder($this->tokenStorage, $this->em, $this->factory);
    }

    public function testBuildAnonUser()
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('anon.'));

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $menu = $this->getMockBuilder('Knp\Menu\ItemInterface')
            ->getMock();
        $menu->expects($this->never())
            ->method('addChild');
        $menu->expects($this->once())
            ->method('setExtra')
            ->with('type', 'pinbar');

        $this->builder->build($menu, array(), 'pinbar');
    }

    public function testBuild()
    {
        $type = 'favorite';
        $userId = 1;
        $user = $this->getMockBuilder('stdClass')
            ->setMethods(array('getId'))
            ->getMock();
        $user->expects($this->once($userId))
            ->method('getId')
            ->will($this->returnValue(1));

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface');
        $this->factory->expects($this->once())
            ->method('createItem')
            ->with($type, array())
            ->will($this->returnValue($item));

        $repository = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Entity\Repository\NavigationItemRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $items = array(
            array('id' => 1, 'title' => 'test1', 'url' => '/', 'type' => $type),
            array('id' => 2, 'title' => 'test2', 'url' => '/home', 'type' => $type)
        );
        $repository->expects($this->once())
            ->method('getNavigationItems')
            ->with($userId, $type)
            ->will($this->returnValue($items));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(get_class($item))
            ->will($this->returnValue($repository));

        $menu = $this->getMockBuilder('Knp\Menu\ItemInterface')
            ->getMock();
        $menu->expects($this->exactly(2))
            ->method('addChild');
        $menu->expects($this->once())
            ->method('setExtra')
            ->with('type', $type);

        $this->builder->build($menu, array(), $type);
    }
}
