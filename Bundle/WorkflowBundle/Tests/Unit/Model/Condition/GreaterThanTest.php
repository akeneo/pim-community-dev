<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\Condition;

class GreaterThanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Condition\GreaterThan
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = new Condition\GreaterThan(new ContextAccessor());
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
            'greater_than' => array(
                'options' => $options,
                'context' => array('foo' => 100, 'bar' => 50),
                'expectedResult' => true
            ),
            'less_than' => array(
                'options' => $options,
                'context' => array('foo' => 50, 'bar' => 100),
                'expectedResult' => false
            ),
            'equal' => array(
                'options' => $options,
                'context' => array('foo' => 50, 'bar' => 50),
                'expectedResult' => false
            ),
        );
    }
}
