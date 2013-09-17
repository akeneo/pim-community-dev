<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\ORM\Query;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Query;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;

class QueryCountCalculatorTest extends \PHPUnit_Framework_TestCase
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
    public function testCalculateCount($dql, array $sqlParameters, array $types, array $queryParameters = array())
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
        return array(
            'empty' => array(
                'dql'           => 'SELECT e FROM Stub:Entity e',
                'sqlParameters' => array(),
                'types'         => array(),
            ),
            'single parameters' => array(
                'dql'             => 'SELECT e FROM Stub:Entity e WHERE e.a = :a AND e.b = :b',
                'sqlParameters'   => array(1, 2),
                'types'           => array(Type::INTEGER, Type::INTEGER),
                'queryParameters' => array('a' => 1, 'b' => 2),
            ),
            'multiple parameters' => array(
                'dql'             => 'SELECT e FROM Stub:Entity e WHERE e.a = :value AND e.b = :value',
                'sqlParameters'   => array(3, 3),
                'types'           => array(Type::INTEGER, Type::INTEGER),
                'queryParameters' => array('value' => 3),
            ),
        );
    }

    /**
     * @return array
     */
    protected function prepareMocks()
    {
        $configuration = new Configuration();
        $configuration->addEntityNamespace('Stub', 'Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM\Stub');

        $classMetadata = new ClassMetadata('Entity');
        $classMetadata->mapField(array('fieldName' => 'a', 'columnName' => 'a'));
        $classMetadata->mapField(array('fieldName' => 'b', 'columnName' => 'b'));

        $platform = $this->getMockBuilder('Doctrine\DBAL\Platforms\AbstractPlatform')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
            ->setMethods(array('fetchColumn'))
            ->disableOriginalConstructor()
            ->getMock();

        $driverConnection = $this->getMockBuilder('Doctrine\DBAL\Driver\Connection')
            ->setMethods(array('query'))
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
            ->setMethods(array('getDatabasePlatform', 'executeQuery'))
            ->setConstructorArgs(array(array(), $driver))
            ->getMock();
        $connection->expects($this->any())
            ->method('getDatabasePlatform')
            ->will($this->returnValue($platform));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('getConfiguration', 'getClassMetadata', 'getConnection'))
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

        return array($entityManager, $connection, $statement);
    }
}
