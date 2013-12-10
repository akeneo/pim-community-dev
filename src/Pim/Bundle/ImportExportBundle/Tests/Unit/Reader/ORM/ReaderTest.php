<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader\ORM;

use Pim\Bundle\ImportExportBundle\Reader\ORM\Reader;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->reader = new Reader();
        $this->stepExecution = $this->getStepExecutionMock();

        $this->reader->setStepExecution($this->stepExecution);
    }

    /**
     * Test related method
     */
    public function testIsAConfigurableStepExecutionAwareReader()
    {
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement', $this->reader);
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\ItemReaderInterface', $this->reader);
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface', $this->reader);
    }

    /**
     * Test related method
     */
    public function testRead()
    {
        $query = $this->getQueryMock(array('foo', 'bar'));
        $this->reader->setQuery($query);

        $this->assertEquals(array('foo', 'bar'), $this->reader->read());
    }

    /**
     * Test related method
     */
    public function testIncrementReadCount()
    {
        $query = $this->getQueryMock(array('foo', 'bar'));
        $this->reader->setQuery($query);
        $this->assertEquals(array('foo', 'bar'), $this->reader->read());
        $this->assertNull($this->reader->read());
    }

    /**
     * Test related method
     */
    public function testOneShotRead()
    {
        $query = $this->getQueryMock(array('foo', 'bar'));
        $this->reader->setQuery($query);

        $this->assertEquals(array('foo', 'bar'), $this->reader->read());
        $this->assertNull($this->reader->read());
    }

    /**
     * Test related method
     */
    public function testNothingToRead()
    {
        $query = $this->getQueryMock(array());
        $this->reader->setQuery($query);
        $stepExecution = $this->getStepExecutionMock();

        $this->assertNull($this->reader->read($stepExecution));
    }

    /**
     * Test related method
     */
    public function testConfigurationFields()
    {
        $this->assertEquals(array(), $this->reader->getConfigurationFields());
    }

    /**
     * @return \Oro\Bundle\BatchBundle\Entity\StepExecution
     */
    private function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $result
     *
     * @return \Doctrine\ORM\AbstractQuery
     */
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
