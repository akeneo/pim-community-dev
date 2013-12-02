<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\SearchBundle\EventListener\IndexListener;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;

class IndexListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var IndexListener
     */
    private $listener;

    /**
     * @var IndexListener
     */
    private $inactiveListener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $engine;

    /**
     * @var OnFlushEventArgs
     */
    private $onFlushArgs;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $postFlushArgs;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $uow;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    public function setUp()
    {
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->engine    = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Engine\Orm')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->onFlushArgs = new OnFlushEventArgs($this->entityManager);
        $this->postFlushArgs = new PostFlushEventArgs($this->entityManager);

        $this->container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo('oro_search.search.engine'))
            ->will($this->returnValue($this->engine));

        $this->uow = $this
            ->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->listener = new IndexListener(
            $this->container,
            true,
            array(
                'Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product' => array(),
                'test_entity2' => array()
            )
        );

        $this->inactiveListener = new IndexListener($this->container, true, array());
    }

    public function testOnFlushAndPostFlushUpdatesIndex()
    {
        $insertEntity = $this->createTestEntity('Insert Entity');
        $updateEntity = $this->createTestEntity('Update Entity');
        $deleteEntity = $this->createTestEntity('Delete Entity');

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue(array($insertEntity)));

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array($updateEntity)));

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue(array($deleteEntity)));

        $this->listener->onFlush($this->onFlushArgs);

        $this->engine->expects($this->once())->method($this->anything());

        $this->engine
            ->expects($this->at(0))
            ->method('save')
            ->with($insertEntity, true, true);

        $this->entityManager->expects($this->once())->method('flush');

        $this->listener->postFlush($this->postFlushArgs);
    }

    public function testOnFlushAndPostFlushNotUpdatesIndexWhenNoEntitiesChanged()
    {
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue(array()));

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array()));

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue(array()));

        $this->engine->expects($this->never())->method($this->anything());
        $this->entityManager->expects($this->never())->method('flush');

        $this->listener->onFlush($this->onFlushArgs);
        $this->listener->postFlush($this->postFlushArgs);
    }

    public function testOnFlushAndPostFlushNotUpdatesIndexWhenNotActive()
    {
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue(array()));

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array()));

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue(array()));

        $this->engine->expects($this->never())->method($this->anything());
        $this->entityManager->expects($this->never())->method('flush');

        $this->listener->onFlush($this->onFlushArgs);
        $this->listener->postFlush($this->postFlushArgs);
    }

    /**
     * @param  string  $name
     * @return Product
     */
    protected function createTestEntity($name)
    {
        $result = new Product();
        $result->setName($name);

        return $result;
    }
}
