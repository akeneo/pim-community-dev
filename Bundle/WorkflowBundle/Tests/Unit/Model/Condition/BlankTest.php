<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\Condition;

class BlankTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\Blank
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = new Condition\Blank(new ContextAccessor());
    }

    /**
     * @dataProvider isAllowedDataProvider
     *
     * @param array $options
     * @param $context
     * @param $expectedResult
     */
    public function testIsAllowed(array $options, $context, $expectedResult)
    {
        $this->condition->initialize($options);
        $this->assertEquals($expectedResult, $this->condition->isAllowed($context));
    }

    public function isAllowedDataProvider()
    {
        return array(
            'not_empty_string' => array(
                'options' => array(new PropertyPath('[foo]')),
                'context' => array('foo' => 'bar'),
                'expectedResult' => false
            ),
            'not_empty_zero' => array(
                'options' => array(new PropertyPath('[foo]')),
                'context' => array('foo' => 0),
                'expectedResult' => false
            ),
            'empty' => array(
                'options' => array(new PropertyPath('[bar]')),
                'context' => array('foo' => 'bar'),
                'expectedResult' => true
            ),
            'empty_string' => array(
                'options' => array(new PropertyPath('[foo]')),
                'context' => array('foo' => ''),
                'expectedResult' => true
            ),
            'empty_null' => array(
                'options' => array(new PropertyPath('[foo]')),
                'context' => array('foo' => null),
                'expectedResult' => true
            ),
        );
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
