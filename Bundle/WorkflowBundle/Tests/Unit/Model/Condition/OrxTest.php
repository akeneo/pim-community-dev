<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class OrxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\Orx
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = new Condition\Orx();
    }

    public function testIsAllowedTrue()
    {
        $this->condition->initialize(
            array(
                new Condition\True(),
                new Condition\False(),
            )
        );
        $this->assertTrue($this->condition->isAllowed('anything'));
    }

    public function testIsAllowedFalse()
    {
        $this->condition->initialize(
            array(
                new Condition\False(),
                new Condition\False(),
            )
        );
        $this->assertFalse($this->condition->isAllowed('anything'));
    }
}
