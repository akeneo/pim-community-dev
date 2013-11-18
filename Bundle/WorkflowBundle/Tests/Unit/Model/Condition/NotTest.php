<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Doctrine\Common\Collections\ArrayCollection;
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

    public function testIsAllowedWithErrors()
    {
        $currentConditionError = 'Current condition error';
        $nestedConditionError = 'Nested condition error';

        $this->condition->setMessage($currentConditionError);

        $falseConditionWithError = new Condition\False();
        $falseConditionWithError->setMessage($nestedConditionError);

        $errors = new ArrayCollection();
        $this->condition->initialize(array($falseConditionWithError));
        $this->assertTrue($this->condition->isAllowed('anything', $errors));
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            array('message' => $nestedConditionError, 'parameters' => array()),
            $errors->get(0)
        );

        $trueConditionWithError = new Condition\True();
        $trueConditionWithError->setMessage($nestedConditionError);

        $errors = new ArrayCollection();
        $this->condition->initialize(array($trueConditionWithError));
        $this->assertFalse($this->condition->isAllowed('anything', $errors));
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            array('message' => $currentConditionError, 'parameters' => array()),
            $errors->get(0)
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionException
     * @expectedExceptionMessage Options must contain an instance of Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface
     */
    // @codingStandardsIgnoreEnd
    public function testInitializeFailsWhenOptionNotConditionInterface()
    {
        $this->condition->initialize(array('anything'));
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
