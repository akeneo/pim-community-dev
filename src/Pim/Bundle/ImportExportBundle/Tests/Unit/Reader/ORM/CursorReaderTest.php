<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader\ORM;

use Pim\Bundle\ImportExportBundle\Reader\ORM\CursorReader;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->reader = new CursorReader();
        $this->stepExecution = $this->getStepExecutionMock();

        $this->reader->setStepExecution($this->stepExecution);
    }

    /**
     * Test related method
     */
    public function testIsAConfigurableStepExecutionAwareReader()
    {
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\ItemReaderInterface', $this->reader);
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement', $this->reader);
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface', $this->reader);
    }

    /**
     * Test related method
     */
    public function testRead()
    {
        $query  = $this->getQueryMock();
        $result = $this->getIterableResultMock(
            [
                $item1 = 'foo',
                $item2 = 'bar',
                $item3 = 'baz',
            ]
        );

        $query->expects($this->any())
            ->method('iterate')
            ->will($this->returnValue($result));

        $this->reader->setQuery($query);
        $this->assertEquals($item1, $this->reader->read());
        $this->assertEquals($item2, $this->reader->read());
        $this->assertEquals($item3, $this->reader->read());
        $this->assertNull($this->reader->read());
    }

    /**
     * @return \octrine\ORM\AbstractQuery
     */
    private function getQueryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->setMethods(['_doExecute', 'getSQL', 'iterate'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $results
     *
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    private function getIterableResultMock(array $results)
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\Internal\Hydration\IterableResult')
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($results as $index => $result) {
            $mock->expects($this->at($index))
                ->method('next')
                ->will($this->returnValue($result));
        }

        return $mock;
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
}
