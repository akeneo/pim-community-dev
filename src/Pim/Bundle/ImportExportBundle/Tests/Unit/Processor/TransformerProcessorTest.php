<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Exception\TranslatableException;
use Pim\Bundle\ImportExportBundle\Processor\TransformerProcessor;
use stdClass;

/**
 * Tests related class
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformerProcessorTest extends AbstractTransformerProcessorTestCase
{
    protected $processor;
    protected $transformer;
    protected $data = array('key' => 'val');
    protected $entity;
    
    protected function setUp()
    {
        parent::setUp();
        $this->transformer = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Transformer\OrmTransformer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processor = new TransformerProcessor(
            $this->validator,
            $this->translator,
            $this->transformer,
            'class'
        );
        $this->entity = new stdClass;
    }

    protected function initializeTransformer()
    {
        $this->transformer
            ->expects($this->once())
            ->method('transform')
            ->with($this->equalTo('class'), $this->equalTo($this->data))
            ->will($this->returnValue($this->entity));
        $this->transformer
            ->expects($this->once())
            ->method('getTransformedColumnsInfo')
            ->will($this->returnValue(array()));
    }

    public function testProcess()
    {
        $this->initializeTransformer();
        $this->transformer
            ->expects($this->once())
            ->method('getErrors')
            ->will($this->returnValue(array()));
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnArgument(4));
        
        $this->assertSame($this->entity, $this->processor->process($this->data));
    }
    
    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\TranslatableException
     * @expectedExceptionMessage <tr>exception value</tr>
     */
    public function testProcessWithTransformerException()
    {
        $this->transformer
            ->expects($this->once())
            ->method('transform')
            ->will(
                $this->throwException(
                    new TranslatableException('exception %arg1%', array('%arg1%'=>'value'))
                )
            );
        $this->processor->process($this->data);
    }
    
    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\TranslatableException
     * @expectedExceptionMessage <tr>exception value</tr>
     */
    public function testProcessWithValidatorException()
    {
        $this->initializeTransformer();
        $this->transformer
            ->expects($this->once())
            ->method('getErrors')
            ->will($this->returnValue(array()));
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will(
                $this->throwException(
                    new TranslatableException('exception %arg1%', array('%arg1%'=>'value'))
                )
            );
        
        $this->processor->process($this->data);
    }

    public function testProcessWithErrors()
    {
        $this->initializeTransformer();
        $this->transformer
            ->expects($this->once())
            ->method('getErrors')
            ->will(
                $this->returnValue(
                    array(
                        'field1' => array(
                            array('exception %arg1%', array('%arg1%'=>'value1')),
                            array('exception %arg2%', array('%arg2%'=>'value1')),
                        ),
                        'field2' => array(
                            array('exception %arg3%', array('%arg3%'=>'value3')),
                        )
                    )
                )
            );
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnArgument(3));
        try {
            $this->processor->process($this->data);
        } catch (InvalidItemException $ex) {
            $message = "field1: <tr>exception value1</tr>,<tr>exception value1</tr>\n" .
                "field2: <tr>exception value3</tr>";
            $this->assertEquals($message, $ex->getMessage());
        }
        $this->assertTrue(isset($ex), 'No exception thrown');
    }
}
