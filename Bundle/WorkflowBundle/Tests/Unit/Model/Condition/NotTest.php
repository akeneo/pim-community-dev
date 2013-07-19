<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class NotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\Not
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = new Condition\Not();
    }

    public function testIsAllowed()
    {
        $this->condition->initialize(array(new Condition\True()));
        $this->assertFalse($this->condition->isAllowed('anything'));

        $this->condition->initialize(array(new Condition\False()));
        $this->assertTrue($this->condition->isAllowed('anything'));
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException
     * @expectedExceptionMessage Options must contain an instance of Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface
     */
    // @codingStandardsIgnoreEnd
    public function testInitializeFailsWhenOptionNotConditionInterface()
    {
        $this->condition->initialize(array('anything'));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException
     * @expectedExceptionMessage Options must have 1 element, but 0 given
     */
    public function testInitializeFailsWhenOptionNotOneElement()
    {
        $this->condition->initialize(array());
    }
}
