<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Oro\Bundle\ImportExportBundle\Processor\ExportProcessor;

class ExportProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExportProcessor
     */
    protected $processor;

    protected function setUp()
    {
        $this->processor = new ExportProcessor();
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\RuntimeException
     * @expectedExceptionMessage Serializer must be injected.
     */
    public function testProcess()
    {
        $entity = $this->getMock('MockEntity');

        $this->processor->process($entity);
    }

    public function testProcessWithDataConverter()
    {
        $entity = $this->getMock('MockEntity');
        $serializedValue = array('serialized');
        $expectedValue = array('expected');

        $serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $serializer->expects($this->once())
            ->method('serialize')
            ->with($entity, null)
            ->will($this->returnValue($serializedValue));

        $dataConverter = $this->getMock('Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface');
        $dataConverter->expects($this->once())
            ->method('convertToExportFormat')
            ->with($serializedValue)
            ->will($this->returnValue($expectedValue));

        $this->processor->setSerializer($serializer);
        $this->processor->setDataConverter($dataConverter);

        $this->assertEquals($expectedValue, $this->processor->process($entity));
    }

    public function testProcessWithoutDataConverter()
    {
        $entity = $this->getMock('MockEntity');
        $expectedValue = array('expected');

        $serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $serializer->expects($this->once())
            ->method('serialize')
            ->with($entity, null)
            ->will($this->returnValue($expectedValue));

        $this->processor->setSerializer($serializer);

        $this->assertEquals($expectedValue, $this->processor->process($entity));
    }

    /*public function testSetImportExportContext()
    {
        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->once())->method('getConfiguration')
            ->will($this->returnValue())

        $this->processor->setSerializer($serializer);

        $this->assertEquals($expectedValue, $this->processor->process($entity));
    }*/
}
