<?php

namespace Oro\Bundle\NavigationBundle\Tests\Entity;

use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;
use Oro\Bundle\NavigationBundle\Entity\Builder\HistoryItemBuilder;

class HistoryItemBuilderTest extends \PHPUnit_Framework_TestCase
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
     * @var HistoryItemBuilder
     */
    protected $builder;

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
        $this->builder = new HistoryItemBuilder($this->em, $this->factory);
    }

    public function testBuildItem()
    {
        $itemBuilder = $this->builder;

        //$user = $this->securityContext->getToken()->getUser();
        $user = $this->getMock('\Oro\Bundle\UserBundle\Entity\User');
        $params = array(
            'title' => 'kldfjs;jasf',
            'url' => 'some url',
            'user' => $user,
        );

        $item = $itemBuilder->buildItem($params);

        $this->assertInstanceOf('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem', $item);
        $this->assertEquals($params['title'], $item->getTitle());
        $this->assertEquals($params['url'], $item->getUrl());
        $this->assertEquals($user, $item->getUser());
        $this->assertInstanceOf('\Oro\Bundle\UserBundle\Entity\User', $item->getUser());
    }

    public function testFindItem()
    {
        $itemBuilder = $this->builder;

        $itemId = 1;
        $this->em
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo('OroNavigationBundle:NavigationHistoryItem'), $this->equalTo($itemId))
            ->will($this->returnValue(new NavigationHistoryItem()));

        $item = $itemBuilder->findItem($itemId);
        $this->assertInstanceOf('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem', $item);
    }
}
