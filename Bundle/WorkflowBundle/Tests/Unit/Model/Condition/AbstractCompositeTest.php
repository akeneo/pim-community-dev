<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class AbstractCompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\AbstractComposite
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = $this->getMockForAbstractClass(
            'Oro\Bundle\WorkflowBundle\Model\Condition\AbstractComposite'
        );
    }

    public function testInitializePasses()
    {
        $conditions = array($this->getMock('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface'));

        $this->condition->initialize($conditions);
        $this->assertAttributeEquals($conditions, 'conditions', $this->condition);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException
     * @expectedExceptionMessage Options must contain an instance of Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface, string is given
     */
    // @codingStandardsIgnoreEnd
    public function testInitializeFailsWithScalarElement()
    {
        $this->condition->initialize(array('anything'));
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionInitializationException
     * @expectedExceptionMessage Options must contain an instance of Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface, stdClass is given
     */
    // @codingStandardsIgnoreEnd
    public function testInitializeFailsWithWrongInstanceElement()
    {
        $this->condition->initialize(array(new \stdClass));
    }
}
