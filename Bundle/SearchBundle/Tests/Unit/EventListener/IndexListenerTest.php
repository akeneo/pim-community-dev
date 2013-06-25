<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\SearchBundle\EventListener\IndexListener;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;

class IndexListenerTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $listener;
    private $engine;
    private $args;
    private $uow;
    private $flushArgs;
    private $testEntity;
    private $emMock;

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

        $this->emMock = $this
            ->getMockBuilder('Doctrine\\ORM\\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->testEntity = new Product();
        $this->args = new LifecycleEventArgs($this->testEntity, $this->emMock);
        $this->flushArgs = new OnFlushEventArgs($this->emMock);

        $this->container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo('oro_search.search.engine'))
            ->will($this->returnValue($this->engine));

        $this->uow = $this
            ->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emMock
            ->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->listener = new IndexListener($this->container, true, $entities);
    }

    public function testOnFlush()
    {
        $entityArray = array($this->testEntity);

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue($entityArray));

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue($entityArray));

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue($entityArray));

        $this->engine
            ->expects($this->any())
            ->method('save')
            ->will($this->returnValue($this->testEntity));

        $this->engine
            ->expects($this->once())
            ->method('delete');

        $reflectionProperty = $this
            ->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $meta = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $meta->expects($this->any())
            ->method('getReflectionProperty')
            ->will($this->returnValue($reflectionProperty));

        $this->emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($meta));

        $meta->expects($this->any())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));

        $reflectionProperty->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1));

        $this->listener->onFlush($this->flushArgs);

        $this->listener->postPersist($this->args);

        $listener = new IndexListener($this->container, true, array());

        $listener->onFlush($this->flushArgs);
    }
}
