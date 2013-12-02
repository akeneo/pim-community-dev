<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class NotHasValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $hasValue;

    /**
     * @var Condition\Blank
     */
    protected $condition;

    protected function setUp()
    {
        $this->hasValue = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\HasValue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->condition = new Condition\NotHasValue($this->hasValue);
    }

    public function testIsAllowed()
    {
        $context = array('foo');

        $this->hasValue->expects($this->once())->method('isAllowed')->with($context);

        $this->condition->isAllowed($context);
    }

    public function testInitialize()
    {
        $options = array('foo');

        $this->hasValue->expects($this->once())->method('initialize')->with($options);

        $this->condition->initialize($options);
    }
}
