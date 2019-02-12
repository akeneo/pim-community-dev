<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\ConfigBundle\Entity\Repository\ConfigValueRepository;

class ConfigValueRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigValueRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $om;

    /**
     * prepare mocks
     */
    public function setUp(): void
    {
        $this->om = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder', 'beginTransaction', 'commit'])
            ->getMock();

        $this->repository = new ConfigValueRepository(
            $this->om,
            new ClassMetadata('Oro\Bundle\ConfigBundle\Entity\Config\Value')
        );
    }

    /**
     * test removeValues
     */
    public function testRemoveValues()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'andWhere', 'where', 'setParameter', 'getQuery'])
            ->getMock();

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('execute');

        $queryBuilder->expects($this->once())
            ->method('delete')
            ->with('OroConfigBundle:ConfigValue', 'cv')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('where')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->om->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $this->om->expects($this->once())
            ->method('beginTransaction');

        $this->om->expects($this->once())
            ->method('commit');

        $removed = [
            ['pim_user', 'level']
        ];

        $this->repository->removeValues(1, $removed);
    }
}
