<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class HasValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextAccessor;

    /**
     * @var Condition\Blank
     */
    protected $condition;

    protected function setUp()
    {
        $this->contextAccessor = $this->getMock('Oro\Bundle\WorkflowBundle\Model\ContextAccessor');
        $this->condition = new Condition\HasValue($this->contextAccessor);
    }

    public function testIsAllowed()
    {
        $context = new \stdClass();
        $path = 'path';
        $expectedValue = true;
        $this->contextAccessor->expects($this->once())->method('hasValue')
            ->with($context, $path)
            ->will($this->returnValue($expectedValue));

        $this->condition->initialize(array($path));
        $this->assertEquals($expectedValue, $this->condition->isAllowed($context));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionException
     * @expectedExceptionMessage Options must have 1 element, but 0 given
     */
    public function testInitializeFailsWhenOptionNotOneElement()
    {
        $this->condition->initialize(array());
    }
}
