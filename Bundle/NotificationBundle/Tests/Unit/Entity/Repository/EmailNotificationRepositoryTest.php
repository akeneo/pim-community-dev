<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Entity\Repository\EmailNotificationRepository;

class EmailNotificationRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_NAME  = 'EmailNotificationEntityName';
    const TEST_NAME    = 'TestEntityName';
    const EVENT_NAME   = 'TestEventName';

    /**
     * @var EmailNotificationRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var EmailNotification
     */
    protected $entity;

    protected $testEntities = array();

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder'))
            ->getMock();

        $this->repository = new EmailNotificationRepository($this->entityManager, new ClassMetadata(self::ENTITY_NAME));
        $this->entity = new EmailNotification();
        $this->testEntities[] = $this->entity;
    }

    protected function tearDown()
    {
        unset($this->repository);
        unset($this->entityManager);
        unset($this->testEntities);
        unset($this->entity);
    }

    public function testGetRules()
    {
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('getResult')
            ->will($this->returnValue($this->testEntities));

        $entityAlias = 'emn';

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('select', 'from', 'getQuery', 'leftJoin'))
            ->getMock();
        $queryBuilder->expects($this->exactly(2))->method('select')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('from')->with(self::ENTITY_NAME, $entityAlias)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('leftJoin')->with($entityAlias . '.event', 'event')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('getQuery')
            ->will($this->returnValue($query));

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $actualResult = $this->repository->getRules();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actualResult);
        $this->assertCount(1, $actualResult);
    }
}
