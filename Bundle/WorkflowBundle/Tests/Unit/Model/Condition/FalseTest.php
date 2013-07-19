<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class FalseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\False
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = new Condition\False();
    }

    public function testIsAllowed()
    {
        $this->assertFalse($this->condition->isAllowed('anything'));
    }

    public function testInitialize()
    {
        $this->assertEquals($this->condition, $this->condition->initialize(array()));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException
     * @expectedExceptionMessage Options are prohibited
     */
    public function testInitializeFails()
    {
        $this->condition->initialize(array('anything'));
    }
}
