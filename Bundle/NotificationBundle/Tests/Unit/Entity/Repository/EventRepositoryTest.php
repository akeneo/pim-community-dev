<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\NotificationBundle\Entity\Event;
use Oro\Bundle\NotificationBundle\Entity\Repository\EventRepository;

class EventRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_NAME = 'EventEntityName';

    /**
     * @var EventRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var Event
     */
    protected $event;

    protected $testEntities = array();

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder'))
            ->getMock();

        $this->repository = new EventRepository($this->entityManager, new ClassMetadata(self::ENTITY_NAME));
        $this->event = new Event('test.event.name');
        $this->testEntities[] = $this->event;
    }

    protected function tearDown()
    {
        unset($this->repository);
        unset($this->entityManager);
        unset($this->testEntities);
        unset($this->event);
    }

    public function testGetEventNames()
    {
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('getResult')
            ->will($this->returnValue($this->testEntities));

        $entityAlias = 'e';

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('select', 'from', 'getQuery'))
            ->getMock();
        $queryBuilder->expects($this->exactly(2))->method('select')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('from')->with(self::ENTITY_NAME, $entityAlias)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('getQuery')
            ->will($this->returnValue($query));

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $actualResult = $this->repository->getEventNames();
        $this->assertEquals($this->testEntities, $actualResult);
    }
}
