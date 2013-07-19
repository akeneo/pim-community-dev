<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\Condition;

class EqualToTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var Condition\EqualTo
     */
    protected $condition;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->condition = new Condition\EqualTo($this->registry, new ContextAccessor());
    }

    public function testIsAllowedTrue()
    {
        $this->condition->initialize(
            array(
                'left' => 'foo',
                'right' => 'bar'
            )
        );
        $context = array(
            'foo' => 'same',
            'bar' => 'same',
        );
        $this->assertFalse($this->condition->isAllowed($context));
    }

    public function testIsAllowedFalse()
    {
        $this->condition->initialize(
            array(
                'left' => 'foo',
                'right' => 'bar'
            )
        );
        $context = array(
            'foo' => 'fooValue',
            'bar' => 'barValue',
        );
        $this->assertFalse($this->condition->isAllowed($context));
    }
}
