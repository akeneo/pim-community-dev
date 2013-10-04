<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Pim\Bundle\ImportExportBundle\Reader\ORMReader;

/**
 * Test related class
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMReaderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->reader = new ORMReader();
    }

    public function testIsAConfigurableReader()
    {
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement', $this->reader);
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\ItemReaderInterface', $this->reader);
    }

    public function testRead()
    {
        $query = $this->getQueryMock(array('foo', 'bar'));
        $this->reader->setQuery($query);
        $stepExecution = $this->getStepExecutionMock();

        $this->assertEquals(array('foo', 'bar'), $this->reader->read($stepExecution));
    }

    public function testIncrementReadCount()
    {
        $query = $this->getQueryMock(array('foo', 'bar'));
        $this->reader->setQuery($query);
        $stepExecution = $this->getStepExecutionMock();
        $stepExecution->expects($this->once())
            ->method('setReadCount')
            ->with(2);

        $this->assertEquals(array('foo', 'bar'), $this->reader->read($stepExecution));
        $this->assertNull($this->reader->read($stepExecution));
    }

    public function testOneShotRead()
    {
        $query = $this->getQueryMock(array('foo', 'bar'));
        $this->reader->setQuery($query);
        $stepExecution = $this->getStepExecutionMock();

        $this->assertEquals(array('foo', 'bar'), $this->reader->read($stepExecution));
        $this->assertNull($this->reader->read($stepExecution));
    }

    public function testNothingToRead()
    {
        $query = $this->getQueryMock(array());
        $this->reader->setQuery($query);
        $stepExecution = $this->getStepExecutionMock();

        $this->assertNull($this->reader->read($stepExecution));
    }

    public function testConfigurationFields()
    {
        $this->assertEquals(array(), $this->reader->getConfigurationFields());
    }

    private function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getQueryMock(array $result)
    {
        $query = $this->getMockForAbstractClass(
            'Doctrine\ORM\AbstractQuery',
            array(),
            '',
            false,
            false,
            true,
            array('execute')
        );

        $query->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($result));

        return $query;
    }
}
