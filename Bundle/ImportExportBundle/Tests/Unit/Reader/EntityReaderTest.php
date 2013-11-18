<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Doctrine\ORM\Query;

use Oro\Bundle\ImportExportBundle\Reader\EntityReader;

class EntityReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextRegistry;

    /**
     * @var EntityReader
     */
    protected $reader;

    protected function setUp()
    {
        $this->contextRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('getByStepExecution'))
            ->getMock();

        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->reader = new EntityReader($this->contextRegistry, $this->managerRegistry);
    }

    public function testReadMockIterator()
    {
        $iterator = $this->getMock('\Iterator');
        $this->managerRegistry->expects($this->never())->method($this->anything());

        $fooEntity = $this->getMock('FooEntity');
        $barEntity = $this->getMock('BarEntity');
        $bazEntity = $this->getMock('BazEntity');

        $iterator->expects($this->at(0))->method('rewind');

        $iterator->expects($this->at(1))->method('valid')->will($this->returnValue(true));
        $iterator->expects($this->at(2))->method('current')->will($this->returnValue($fooEntity));
        $iterator->expects($this->at(3))->method('next');

        $iterator->expects($this->at(4))->method('valid')->will($this->returnValue(true));
        $iterator->expects($this->at(5))->method('current')->will($this->returnValue($barEntity));
        $iterator->expects($this->at(6))->method('next');

        $iterator->expects($this->at(7))->method('valid')->will($this->returnValue(true));
        $iterator->expects($this->at(8))->method('current')->will($this->returnValue($bazEntity));
        $iterator->expects($this->at(9))->method('next');

        $iterator->expects($this->at(10))->method('valid')->will($this->returnValue(false));
        $iterator->expects($this->at(11))->method('valid')->will($this->returnValue(false));

        $this->reader->setSourceIterator($iterator);

        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')->getMock();
        $context->expects($this->exactly(3))->method('incrementReadOffset');
        $context->expects($this->exactly(3))->method('incrementReadCount');

        $stepExecution = $this->getMockStepExecution($context);
        $this->reader->setStepExecution($stepExecution);

        $this->assertEquals($fooEntity, $this->reader->read());
        $this->assertEquals($barEntity, $this->reader->read());
        $this->assertEquals($bazEntity, $this->reader->read());
        $this->assertNull($this->reader->read());
        $this->assertNull($this->reader->read());
    }

    public function testReadRealIterator()
    {
        $this->managerRegistry->expects($this->never())->method($this->anything());

        $fooEntity = $this->getMock('FooEntity');
        $barEntity = $this->getMock('BarEntity');
        $bazEntity = $this->getMock('BazEntity');

        $iterator = new \ArrayIterator(array($fooEntity, $barEntity, $bazEntity));

        $this->reader->setSourceIterator($iterator);

        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')->getMock();
        $context->expects($this->exactly(3))->method('incrementReadOffset');
        $context->expects($this->exactly(3))->method('incrementReadCount');

        $stepExecution = $this->getMockStepExecution($context);
        $this->reader->setStepExecution($stepExecution);

        $this->assertEquals($fooEntity, $this->reader->read());
        $this->assertEquals($barEntity, $this->reader->read());
        $this->assertEquals($bazEntity, $this->reader->read());
        $this->assertNull($this->reader->read());
        $this->assertNull($this->reader->read());
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\LogicException
     * @expectedExceptionMessage Reader must be configured with source
     */
    public function testReadFailsWhenNoSourceIterator()
    {
        $this->managerRegistry->expects($this->never())->method($this->anything());

        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->never())->method($this->anything());
        $this->reader->read($stepExecution);
    }

    public function testSetStepExecutionWithQueryBuilder()
    {
        $this->managerRegistry->expects($this->never())->method($this->anything());

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();

        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')->getMock();
        $context->expects($this->at(0))->method('hasOption')->with('entityName')->will($this->returnValue(false));
        $context->expects($this->at(1))->method('hasOption')->with('queryBuilder')->will($this->returnValue(true));
        $context->expects($this->at(2))->method('getOption')
            ->with('queryBuilder')
            ->will($this->returnValue($queryBuilder));

        $this->reader->setStepExecution($this->getMockStepExecution($context));

        $this->assertAttributeInstanceOf(
            'Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator',
            'sourceIterator',
            $this->reader
        );

        $this->assertAttributeEquals(
            $queryBuilder,
            'source',
            self::readAttribute($this->reader, 'sourceIterator')
        );
    }

    public function testSetStepExecutionWithQuery()
    {
        $this->managerRegistry->expects($this->never())->method($this->anything());

        $query = new Query(
            $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock()
        );

        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')->getMock();
        $context->expects($this->at(0))->method('hasOption')->with('entityName')->will($this->returnValue(false));
        $context->expects($this->at(1))->method('hasOption')->with('queryBuilder')->will($this->returnValue(false));
        $context->expects($this->at(2))->method('hasOption')->with('query')->will($this->returnValue(true));
        $context->expects($this->at(3))->method('getOption')
            ->with('query')
            ->will($this->returnValue($query));

        $this->reader->setStepExecution($this->getMockStepExecution($context));

        $this->assertAttributeInstanceOf(
            'Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator',
            'sourceIterator',
            $this->reader
        );

        $this->assertAttributeEquals(
            $query,
            'source',
            self::readAttribute($this->reader, 'sourceIterator')
        );
    }

    public function testSetStepExecutionWithEntityName()
    {
        $entityName = 'entityName';

        $classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')->disableOriginalConstructor()
            ->getMock();

        $classMetadata->expects($this->once())->method('getAssociationMappings')->will($this->returnValue(array()));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $entityManager->expects($this->once())->method('getClassMetadata')
            ->with($entityName)
            ->will($this->returnValue($classMetadata));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();

        $queryBuilder->expects($this->once())->method('getEntityManager')
            ->will($this->returnValue($entityManager));

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('createQueryBuilder')
            ->with('o')
            ->will($this->returnValue($queryBuilder));

        $this->managerRegistry->expects($this->once())->method('getRepository')
            ->with($entityName)
            ->will($this->returnValue($repository));

        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')->getMock();
        $context->expects($this->at(0))->method('hasOption')->with('entityName')->will($this->returnValue(true));
        $context->expects($this->at(1))->method('getOption')
            ->with('entityName')
            ->will($this->returnValue($entityName));

        $this->reader->setStepExecution($this->getMockStepExecution($context));

        $this->assertAttributeInstanceOf(
            'Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator',
            'sourceIterator',
            $this->reader
        );

        $this->assertAttributeEquals(
            $queryBuilder,
            'source',
            self::readAttribute($this->reader, 'sourceIterator')
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Configuration of entity reader must contain either "entityName", "queryBuilder" or "query".
     */
    // @codingStandardsIgnoreEnd
    public function testSetStepExecutionFailsWhenHasNoRequiredOptions()
    {
        $this->managerRegistry->expects($this->never())->method($this->anything());

        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')->getMock();
        $context->expects($this->exactly(3))->method('hasOption')->will($this->returnValue(false));

        $this->reader->setStepExecution($this->getMockStepExecution($context));
    }

    public function testSetSourceEntityName()
    {
        $name = '\stdClass';

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $classMetadata->expects($this->once())
            ->method('getAssociationMappings')
            ->will(
                $this->returnValue(
                    array(
                        array('fieldName' => 'test')
                    )
                )
            );
        $queryBuilder->expects($this->once())
            ->method('addSelect')
            ->with('_test');
        $queryBuilder->expects($this->once())
            ->method('leftJoin')
            ->with('o.test', '_test');

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('o')
            ->will($this->returnValue($queryBuilder));

        $this->managerRegistry->expects($this->once())
            ->method('getRepository')
            ->with($name)
            ->will($this->returnValue($repository));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $entityManager->expects($this->once())->method('getClassMetadata')
            ->with($name)
            ->will($this->returnValue($classMetadata));

        $queryBuilder->expects($this->once())->method('getEntityManager')
            ->will($this->returnValue($entityManager));

        $this->reader->setSourceEntityName($name);
    }

    /**
     * @param mixed $context
     * @return \PHPUnit_Framework_MockObject_MockObject+
     */
    protected function getMockStepExecution($context)
    {
        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextRegistry->expects($this->any())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        return $stepExecution;
    }
}
