<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class NotEqualToTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\EqualTo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $equalTo;

    /**
     * @var Condition\NotEqualTo
     */
    protected $condition;

    protected function setUp()
    {
        $this->equalTo = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\EqualTo')
            ->disableOriginalConstructor()
            ->setMethods(array('initialize', 'isAllowed'))
            ->getMock();
        $this->condition = new Condition\NotEqualTo($this->equalTo);
    }

    public function testIsAllowed()
    {
        $context = array('foo' => 'fooValue', 'bar' => 'barValue');

        $this->equalTo->expects($this->once())->method('isAllowed')->with($context);

        $this->condition->isAllowed($context);
    }

    public function testInitialize()
    {
        $options = array('left' => 'foo', 'right' => 'bar');

        $this->equalTo->expects($this->once())->method('initialize')->with($options);

        $this->condition->initialize($options);
    }
}
