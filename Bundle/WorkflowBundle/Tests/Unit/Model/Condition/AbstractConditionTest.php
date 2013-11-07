<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition;

class AbstractConditionTest extends \PHPUnit_Framework_TestCase
{
    public function testMessages()
    {
        /** @var AbstractCondition $condition */
        $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition')
            ->getMockForAbstractClass();
        $this->assertSame($condition, $condition->setMessage('Test'));
        $this->assertEquals('Test', $condition->getMessage());
    }
}
