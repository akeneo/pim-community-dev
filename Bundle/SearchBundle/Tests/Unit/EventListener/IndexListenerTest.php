<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\SearchBundle\EventListener\IndexListener;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;

class IndexListenerTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $listener;
    private $engine;
    private $args;

    public function setUp()
    {
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->engine    = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Engine\Orm')
            ->disableOriginalConstructor()
            ->getMock();

        $entities = array(
            'Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product' => array(),
            'test_entity2' => array()
        );

        $emMock = $this
            ->getMockBuilder('Doctrine\\ORM\\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->args = new LifecycleEventArgs(new Product(), $emMock);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('oro_search.search.engine'))
            ->will($this->returnValue($this->engine));

        $this->listener = new IndexListener($this->container, true, $entities);
    }

    public function testPostPersist()
    {
        $this->engine
            ->expects($this->once())
            ->method('save');

        $this->listener->postPersist($this->args);

        $listener = new IndexListener($this->container, true, array());

        $listener->postPersist($this->args);
    }

    public function testPreRemove()
    {
        $this->engine
            ->expects($this->once())
            ->method('delete');

        $this->listener->preRemove($this->args);

        $listener = new IndexListener($this->container, true, array());

        $listener->preRemove($this->args);
    }

    public function testPostUpdate()
    {
        $this->engine
            ->expects($this->once())
            ->method('save');

        $this->listener->postUpdate($this->args);

        $listener = new IndexListener($this->container, true, array());

        $listener->postUpdate($this->args);
    }
}
