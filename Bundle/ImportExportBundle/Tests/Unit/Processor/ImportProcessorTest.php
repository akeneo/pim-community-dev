<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Oro\Bundle\ImportExportBundle\Processor\ImportProcessor;

class ImportProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImportProcessor
     */
    protected $processor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var array
     */
    protected $item = array('test' => 'test');

    /**
     * @var object
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new \stdClass();

        $this->context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->setMethods(array('getOption'))
            ->getMockForAbstractClass();
        $this->context->expects($this->once())
            ->method('getOption', 'addError')
            ->with('entityName')
            ->will($this->returnValue('\stdClass'));

        $this->serializer = $this->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')
            ->getMockForAbstractClass();
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($this->item, '\stdClass', null)
            ->will($this->returnValue($this->object));

        $this->processor = new ImportProcessor();
        $this->processor->setSerializer($this->serializer);
        $this->processor->setImportExportContext($this->context);
    }

    public function testProcessMinimum()
    {
        $this->assertEquals($this->object, $this->processor->process($this->item));
    }

    public function testProcessInvalidObject()
    {
        $violation = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolation')
            ->disableOriginalConstructor()
            ->getMock();
        $violation->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('Error'));
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->once())
            ->method('validate')
            ->with($this->object)
            ->will($this->returnValue(array($violation)));
        $this->context->expects($this->once())
            ->method('addError')
            ->with('Error');

        $this->processor->setValidator($validator);
        $this->assertNull($this->processor->process($this->item));
    }

    public function testProcess()
    {
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->once())
            ->method('validate')
            ->with($this->object)
            ->will($this->returnValue(array()));
        $this->context->expects($this->never())
            ->method('addError');

        $converter = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface')
            ->setMethods(array('convertToImportFormat'))
            ->getMockForAbstractClass();
        $converter->expects($this->once())
            ->method('convertToImportFormat')
            ->with($this->item)
            ->will($this->returnArgument(0));

        $strategy = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface')
            ->setMethods(array('process'))
            ->getMockForAbstractClass();
        $strategy->expects($this->once())
            ->method('process')
            ->with($this->object)
            ->will($this->returnArgument(0));

        $this->processor->setValidator($validator);
        $this->processor->setDataConverter($converter);
        $this->processor->setStrategy($strategy);
        $this->assertEquals($this->object, $this->processor->process($this->item));
    }
}
