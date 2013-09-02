<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\CallMethod;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;

class CallMethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextAccessor;

    /**
     * @var CallMethod
     */
    protected $postAction;

    protected function setUp()
    {
        $this->contextAccessor = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\ContextAccessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->postAction = new CallMethod($this->contextAccessor);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Method name parameter is required
     */
    public function testInitializeException()
    {
        $this->postAction->initialize(array());
    }

    public function testInitialize()
    {
        $options = array(
            'method' => 'test',
            'object' => new \stdClass(),
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
        $context = array();
        $options = array(
            'method' => function ($a) {
                \PHPUnit_Framework_Assert::assertEquals('test', $a);
                return 'bar';
            },
            'method_parameters' => array('test'),
            'attribute' => 'test'
        );
        $this->contextAccessor->expects($this->once())
            ->method('setValue')
            ->with($context, 'test', 'bar');

        $this->postAction->initialize($options);
        $this->postAction->execute($context);
    }

    public function testExecuteClassMethod()
    {
        $context = array();
        $options = array(
            'method' => 'assertCall',
            'object' => $this,
            'method_parameters' => array('test'),
            'attribute' => 'test'
        );
        $this->contextAccessor->expects($this->once())
            ->method('setValue')
            ->with($context, 'test', 'bar');
        $this->postAction->initialize($options);
        $this->postAction->execute($context);
    }

    public function testExecuteClassMethodNoAssign()
    {
        $context = array();
        $options = array(
            'method' => 'assertCall',
            'object' => $this,
            'method_parameters' => array('test')
        );
        $this->contextAccessor->expects($this->never())
            ->method('setValue');
        $this->postAction->initialize($options);
        $this->postAction->execute($context);
    }

    public function assertCall($a)
    {
        $this->assertEquals('test', $a);
        return 'bar';
    }
}
