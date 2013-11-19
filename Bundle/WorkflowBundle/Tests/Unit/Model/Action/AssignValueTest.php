<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\AssignValue;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;

class AssignValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ActionInterface
     */
    protected $action;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextAccessor;

    protected function setUp()
    {
        $this->contextAccessor = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\ContextAccessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->action = new AssignValue($this->contextAccessor);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute and value parameters are required.
     * @dataProvider invalidOptionsNumberDataProvider
     * @param array $options
     */
    public function testInitializeExceptionParametersCount($options)
    {
        $this->action->initialize($options);
    }

    public function invalidOptionsNumberDataProvider()
    {
        return array(
            array(array()),
            array(array(1)),
            array(array(1, 2, 3)),
            array(array('target' => 1)),
            array(array('value' => 1)),
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute must be valid property definition.
     * @dataProvider invalidOptionsAttributeDataProvider
     * @param array $options
     */
    public function testInitializeExceptionInvalidAttribute($options)
    {
        $this->action->initialize($options);
    }

    public function invalidOptionsAttributeDataProvider()
    {
        return array(
            array(array('test', 'value')),
            array(array('attribute' => 'test', 'value' => 'value'))
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute must be defined.
     */
    public function testInitializeExceptionNoAttribute()
    {
        $this->action->initialize(array('some' => 'test', 'value' => 'test'));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Value must be defined.
     */
    public function testInitializeExceptionNoValue()
    {
        $this->action->initialize(array('attribute' => 'test', 'unknown' => 'test'));
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     */
    public function testInitialize($options)
    {
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface',
            $this->action->initialize($options)
        );

        if (is_array(current($options))) {
            $expectedAssigns = array_values($options);
        } else {
            $expectedAssigns[] = $options;
        }

        $this->assertAttributeEquals($expectedAssigns, 'assigns', $this->action);
    }

    public function optionsDataProvider()
    {
        $assigns = array(
            'numeric arguments' => array(
                'options' => array($this->getPropertyPath(), 'value')
            ),
            'string arguments' => array(
                'options' => array('attribute' => $this->getPropertyPath(), 'value' => 'value')
            ),
            'numeric null value' => array(
                'options' => array($this->getPropertyPath(), null)
            ),
            'string null value' => array(
                'options' => array('attribute' => $this->getPropertyPath(), 'value' => null)
            ),
        );

        // unite all single assigns to one mass assign
        $assigns['mass assign'] = $assigns;

        return $assigns;
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     */
    public function testExecute($options)
    {
        $context = array();
        $optionsData = array_values($options);
        if (is_array(current($optionsData))) {
            for ($i = 0; $i < count($optionsData); $i++) {
                $assignData = array_values($optionsData[$i]);
                $attribute = $assignData[0];
                $value = $assignData[1];
                $this->contextAccessor->expects($this->at($i))
                    ->method('setValue')
                    ->with($context, $attribute, $value);
            }
        } else {
            $attribute = $optionsData[0];
            $value = $optionsData[1];
            $this->contextAccessor->expects($this->once())
                ->method('setValue')
                ->with($context, $attribute, $value);
        }

        $this->action->initialize($options);
        $this->action->execute($context);
    }

    protected function getPropertyPath()
    {
        return $this->getMockBuilder('Symfony\Component\PropertyAccess\PropertyPath')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
