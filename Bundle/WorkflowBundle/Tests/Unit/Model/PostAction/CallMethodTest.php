<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\PostAction\CallMethod;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub;

class CallMethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CallMethod
     */
    protected $postAction;

    protected function setUp()
    {
        $this->postAction = new CallMethod(new ContextAccessor());
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Method name parameter is required
     */
    public function testInitializeNoMethod()
    {
        $this->postAction->initialize(array());
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Object must be valid property definition
     */
    public function testInitializeInvalidObject()
    {
        $this->postAction->initialize(
            array(
                'method' => 'do',
                'object' => 'stringData'
            )
        );
    }

    public function testInitialize()
    {
        $options = array(
            'method' => 'test',
            'object' => new PropertyPath('object'),
            'method_parameters' => null,
            'attribute' => 'test'
        );
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface',
            $this->postAction->initialize($options)
        );
        $this->assertAttributeEquals($options, 'options', $this->postAction);
    }

    public function testExecuteMethod()
    {
        $context = new ItemStub(array('key' => 'value'));
        $options = array(
            'method' => function ($a) {
                \PHPUnit_Framework_Assert::assertEquals('value', $a);
                return 'bar';
            },
            'method_parameters' => array(new PropertyPath('key')),
            'attribute' => 'test'
        );

        $this->postAction->initialize($options);
        $this->postAction->execute($context);

        $this->assertEquals(array('key' => 'value', 'test' => 'bar'), $context->getData());
    }

    public function testExecuteClassMethod()
    {
        $context = new ItemStub(array('object' => $this));
        $options = array(
            'method' => 'assertCall',
            'object' => new PropertyPath('object'),
            'method_parameters' => array('test'),
            'attribute' => 'test'
        );

        $this->postAction->initialize($options);
        $this->postAction->execute($context);

        $this->assertEquals(array('object' => $this, 'test' => 'bar'), $context->getData());
    }

    public function testExecuteClassMethodNoAssign()
    {
        $context = new ItemStub(array('object' => $this));
        $options = array(
            'method' => 'assertCall',
            'object' => new PropertyPath('object'),
            'method_parameters' => array('test')
        );

        $this->postAction->initialize($options);
        $this->postAction->execute($context);

        $this->assertEquals(array('object' => $this), $context->getData());
    }

    public function assertCall($a)
    {
        $this->assertEquals('test', $a);
        return 'bar';
    }
}
