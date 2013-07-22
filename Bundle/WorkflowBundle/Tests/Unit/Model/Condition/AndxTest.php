<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class AndxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\Andx
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = new Condition\Andx();
    }

    public function testIsAllowedTrue()
    {
        $this->condition->initialize(
            array(
                new Condition\True(),
                new Condition\True(),
            )
        );
        $this->assertTrue($this->condition->isAllowed('anything'));
    }

    public function testIsAllowedFalse()
    {
        $this->condition->initialize(
            array(
                new Condition\True(),
                new Condition\False(),
            )
        );
        $this->assertFalse($this->condition->isAllowed('anything'));
    }

    public function testIsAllowedEmpty()
    {
        $this->assertFalse($this->condition->isAllowed('anything'));
    }
}
