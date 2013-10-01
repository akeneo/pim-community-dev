<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition;

class NotBlankTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\EqualTo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blank;

    /**
     * @var Condition\NotBlank
     */
    protected $condition;

    protected function setUp()
    {
        $this->blank = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\Blank')
            ->disableOriginalConstructor()
            ->setMethods(array('initialize', 'isAllowed'))
            ->getMock();
        $this->condition = new Condition\NotBlank($this->blank);
    }

    public function testIsAllowed()
    {
        $context = array('fooValue');

        $this->blank->expects($this->once())->method('isAllowed')->with($context);

        $this->condition->isAllowed($context);
    }

    public function testInitialize()
    {
        $options = array('foo');

        $this->blank->expects($this->once())->method('initialize')->with($options);

        $this->condition->initialize($options);
    }
}
