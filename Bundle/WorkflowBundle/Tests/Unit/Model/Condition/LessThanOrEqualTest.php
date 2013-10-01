<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\Condition;

class LessThanOrEqualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\LessThanOrEqual
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = new Condition\LessThanOrEqual(new ContextAccessor());
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
        $options = array('left' => new PropertyPath('[foo]'), 'right' => new PropertyPath('[bar]'));
        return array(
            'less_than' => array(
                'options' => $options,
                'context' => array('foo' => 50, 'bar' => 100),
                'expectedResult' => true
            ),
            'equal' => array(
                'options' => $options,
                'context' => array('foo' => 50, 'bar' => 50),
                'expectedResult' => true
            ),
            'greater_than' => array(
                'options' => $options,
                'context' => array('foo' => 100, 'bar' => 50),
                'expectedResult' => false
            ),
        );
    }
}
