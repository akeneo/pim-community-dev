<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\ORM\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\DataGridBundle\ORM\Query\QueryCountCalculator;

class CountCalculatorTest extends \PHPUnit_Framework_TestCase
{
    const TEST_COUNT = 42;

    /**
     * @param string $dql
     * @param array $sqlParameters
     * @param array $types
     * @param array $queryParameters
     *
     * @dataProvider getCountDataProvider
     */
    public function testCalculateCount($dql, array $sqlParameters, array $types, array $queryParameters = [])
    {
        /** @var $entityManager EntityManager|\PHPUnit_Framework_MockObject_MockObject */
        /** @var $connection Connection|\PHPUnit_Framework_MockObject_MockObject */
        /** @var $statement Statement|\PHPUnit_Framework_MockObject_MockObject */
        list($entityManager, $connection, $statement) = $this->prepareMocks();

        $query = new Query($entityManager);
        $query->setDQL($dql);
        $query->setParameters($queryParameters);

        $expectedSql = 'SELECT COUNT(*) FROM (' . $query->getSQL() .') AS e';
        $connection->expects($this->once())
            ->method('executeQuery')
            ->with($expectedSql, $sqlParameters, $types)
            ->will($this->returnValue($statement));

        $statement->expects($this->once())
            ->method('fetchColumn')
            ->with()
            ->will($this->returnValue(self::TEST_COUNT));

        $this->assertEquals(self::TEST_COUNT, QueryCountCalculator::calculateCount($query));
    }

    /**
     * @return array
     */
    public function getCountDataProvider()
    {
        return [
            'empty' => [
                'dql'           => 'SELECT e FROM Stub:Entity e',
                'sqlParameters' => [],
                'types'         => [],
            ],
            'single parameters' => [
                'dql'             => 'SELECT e FROM Stub:Entity e WHERE e.a = :a AND e.b = :b',
                'sqlParameters'   => [1, 2],
                'types'           => [Type::INTEGER, Type::INTEGER],
                'queryParameters' => ['a' => 1, 'b' => 2],
            ],
            'multiple parameters' => [
                'dql'             => 'SELECT e FROM Stub:Entity e WHERE e.a = :value AND e.b = :value',
                'sqlParameters'   => [3, 3],
                'types'           => [Type::INTEGER, Type::INTEGER],
                'queryParameters' => ['value' => 3],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function prepareMocks()
    {
        $configuration = new Configuration();

        $configuration->addEntityNamespace('Stub', 'Oro\Bundle\DataGridBundle\Tests\Unit\ORM\Query\Stub');

        $classMetadata = new ClassMetadata('Entity');
        $classMetadata->mapField(['fieldName' => 'a', 'columnName' => 'a']);
        $classMetadata->mapField(['fieldName' => 'b', 'columnName' => 'b']);

        $platform = $this->getMockBuilder('Doctrine\DBAL\Platforms\AbstractPlatform')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
            ->setMethods(['fetchColumn'])
            ->disableOriginalConstructor()
            ->getMock();

        $driverConnection = $this->getMockBuilder('Doctrine\DBAL\Driver\Connection')
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $driverConnection->expects($this->any())
            ->method('query')
            ->will($this->returnValue($statement));

        $driver = $this->getMockBuilder('Doctrine\DBAL\Driver')
            ->setMethods('connect', 'getDatabasePlatform')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $driver->expects($this->any())
            ->method('connect')
            ->will($this->returnValue($driverConnection));
        $driver->expects($this->any())
            ->method('getDatabasePlatform')
            ->will($this->returnValue($platform));

        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->setMethods(['getDatabasePlatform', 'executeQuery'])
            ->setConstructorArgs([[], $driver])
            ->getMock();
        $connection->expects($this->any())
            ->method('getDatabasePlatform')
            ->will($this->returnValue($platform));

        /** @var UnitOfWork $unitOfWork */
        $unitOfWork = $this->getMockBuilder('UnitOfWork')
            ->setMethods(['getEntityPersister'])
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(['getConfiguration', 'getClassMetadata', 'getConnection', 'getUnitOfWork'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));
        $entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($classMetadata));
        $entityManager->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));
        $entityManager->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($unitOfWork));

        return [$entityManager, $connection, $statement];
    }
}
