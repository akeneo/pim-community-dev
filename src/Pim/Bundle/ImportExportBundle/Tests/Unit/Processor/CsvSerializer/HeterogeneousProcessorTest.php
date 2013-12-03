<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor\CsvSerializer;

use Pim\Bundle\ImportExportBundle\Processor\CsvSerializer\HeterogeneousProcessor;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HeterogeneousProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->serializer = $this
            ->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new HeterogeneousProcessor($this->serializer);
    }

    /**
     * Test related method
     */
    public function testProcess()
    {
        $stepExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor->setStepExecution($stepExecution);
        $this->processor->setDelimiter(';');
        $this->processor->setEnclosure('"');
        $this->processor->setWithHeader(true);
        $item = new \StdClass();

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $item,
                'csv',
                array(
                    'delimiter'     => ';',
                    'enclosure'     => '"',
                    'withHeader'    => true,
                    'heterogeneous' => true,
                )
            )
            ->will($this->returnValue('serialized'));

        $this->assertEquals('serialized', $this->processor->process($item));
    }
}
