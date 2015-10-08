<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Menu;

use Oro\Bundle\NavigationBundle\Menu\NavigationMostviewedBuilder;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;

class NavigationMostviewedBuilderTest extends \PHPUnit_Framework_TestCase
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
     * @var NavigationMostviewedBuilder
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

        $this->builder = new NavigationMostviewedBuilder($this->tokenStorage, $this->em, $this->factory);
    }

    public function testBuild()
    {
        $type = 'mostviewed';
        $maxItems = 20;
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

        $this->tokenStorage->expects($this->once())
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

        $repository->expects($this->once())
            ->method('getNavigationItems')
            ->with(
                $userId,
                $type,
                array(
                    'maxItems' => $maxItems,
                    'orderBy' => array(array('field' => NavigationHistoryItem::NAVIGATION_HISTORY_COLUMN_VISIT_COUNT))
                )
            )
            ->will($this->returnValue(array()));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(get_class($item))
            ->will($this->returnValue($repository));

        $configMock = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\UserConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $configMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('oro_navigation.maxItems'))
            ->will($this->returnValue($maxItems));

        $menu = $this->getMockBuilder('Knp\Menu\ItemInterface')->getMock();

        $this->builder->setOptions($configMock);
        $this->builder->build($menu, array(), $type);
    }
}
