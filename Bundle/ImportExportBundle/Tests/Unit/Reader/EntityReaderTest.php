<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Doctrine\ORM\Query;

use Oro\Bundle\ImportExportBundle\Reader\EntityReader;

class EntityReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var EntityReader
     */
    protected $reader;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->reader = new EntityReader($this->registry);
    }

    public function testReadMockIterator()
    {
        $iterator = $this->getMock('\Iterator');
        $this->registry->expects($this->never())->method($this->anything());

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

        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->exactly(3))->method('incrementReadCount');

        $this->assertEquals($fooEntity, $this->reader->read($stepExecution));
        $this->assertEquals($barEntity, $this->reader->read($stepExecution));
        $this->assertEquals($bazEntity, $this->reader->read($stepExecution));
        $this->assertNull($this->reader->read($stepExecution));
        $this->assertNull($this->reader->read($stepExecution));
    }

    public function testReadRealIterator()
    {
        $this->registry->expects($this->never())->method($this->anything());

        $fooEntity = $this->getMock('FooEntity');
        $barEntity = $this->getMock('BarEntity');
        $bazEntity = $this->getMock('BazEntity');

        $iterator = new \ArrayIterator(array($fooEntity, $barEntity, $bazEntity));

        $this->reader->setSourceIterator($iterator);

        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->exactly(3))->method('incrementReadCount');

        $this->assertEquals($fooEntity, $this->reader->read($stepExecution));
        $this->assertEquals($barEntity, $this->reader->read($stepExecution));
        $this->assertEquals($bazEntity, $this->reader->read($stepExecution));
        $this->assertNull($this->reader->read($stepExecution));
        $this->assertNull($this->reader->read($stepExecution));
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\LogicException
     * @expectedExceptionMessage Reader must be configured with source
     */
    public function testReadFailsWhenNoSourceIterator()
    {
        $this->registry->expects($this->never())->method($this->anything());

        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->never())->method($this->anything());
        $this->reader->read($stepExecution);
    }

    public function testSetStepExecutionWithQueryBuilder()
    {
        $this->registry->expects($this->never())->method($this->anything());

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();
        $this->reader->setStepExecution($this->getMockStepExecution(array('queryBuilder' => $queryBuilder)));

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
        $this->registry->expects($this->never())->method($this->anything());

        $query = new Query(
            $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock()
        );
        $this->reader->setStepExecution($this->getMockStepExecution(array('query' => $query)));

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

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())->method('createQueryBuilder')
            ->with('o')
            ->will($this->returnValue($queryBuilder));

        $this->registry->expects($this->once())->method('getRepository')
            ->with($entityName)
            ->will($this->returnValue($repository));

        $this->reader->setStepExecution($this->getMockStepExecution(array('entityName' => $entityName)));

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
        $this->registry->expects($this->never())->method($this->anything());

        $this->reader->setStepExecution($this->getMockStepExecution(array()));
    }

    /**
     * @param array $jobInstanceRawConfiguration
     * @return \PHPUnit_Framework_MockObject_MockObject+
     */
    protected function getMockStepExecution(array $jobInstanceRawConfiguration)
    {
        $jobInstance = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobInstance')
            ->disableOriginalConstructor()
            ->getMock();

        $jobInstance->expects($this->once())->method('getRawConfiguration')
            ->will($this->returnValue($jobInstanceRawConfiguration));

        $jobExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $jobExecution->expects($this->once())->method('getJobInstance')
            ->will($this->returnValue($jobInstance));

        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->once())->method('getJobExecution')
            ->will($this->returnValue($jobExecution));

        return $stepExecution;
    }
}
