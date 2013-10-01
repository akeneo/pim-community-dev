<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\Item\Support;

use Oro\Bundle\BatchBundle\Item\Support\ArrayReader;

/**
 * Tests related to the ArrayReader class
 *
 */
class ArrayReaderTest extends \PHPUnit_Framework_TestCase
{
    protected $arrayReader = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->arrayReader = new ArrayReader();
    }

    public function testReadNull()
    {
        $stepExecution = $this->getStepExecutionMock();
        $this->assertNull($this->arrayReader->read($stepExecution));
    }

    public function testRead()
    {
        $stepExecution = $this->getStepExecutionMock();
        $items = array('item1', 'item2', 'item3');
        $this->assertEntity($this->arrayReader->setItems($items));

        $readItems = array();

        while (($item = $this->arrayReader->read($stepExecution))) {
            $readItems[] = $item;
        }

        $this->assertEquals($items, $readItems);
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\Support\ArrayReader', $entity);
    }

    private function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
