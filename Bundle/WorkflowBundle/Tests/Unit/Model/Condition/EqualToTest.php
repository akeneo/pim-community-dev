<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\Condition;
use Oro\Bundle\WorkflowBundle\Model\DoctrineHelper;

class EqualToTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var Condition\EqualTo
     */
    protected $condition;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockDoctrineHelper();
        $this->condition = new Condition\EqualTo(new ContextAccessor(), $this->doctrineHelper);
    }

    /**
     * @dataProvider isAllowedDataProvider
     *
     * @param array $options
     * @param $context
     * @param array $expectedCalls
     * @param $expectedResult
     */
    public function testIsAllowed(array $options, $context, array $expectedCalls, $expectedResult)
    {
        foreach ($expectedCalls as $key => $calls) {
            $this->addMockExpectedCalls($key, $calls);
        }

        $this->condition->initialize($options);
        $this->assertEquals($expectedResult, $this->condition->isAllowed($context));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDoctrineHelper()
    {
        if (!$this->doctrineHelper) {
            $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\DoctrineHelper')
                ->disableOriginalConstructor()
                ->getMock();
        }
        return $this->doctrineHelper;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function isAllowedDataProvider()
    {
        $options = array('left' => new PropertyPath('[foo]'), 'right' => new PropertyPath('[bar]'));

        return array(
            'scalars_equal' => array(
                'options' => $options,
                'context' => array('foo' => 'value', 'bar' => 'value'),
                'expectedCalls' => array('doctrineHelper' => array()),
                'expectedResult' => true
            ),
            'scalars_not_equal' => array(
                'options' => $options,
                'context' => array('foo' => 'fooValue', 'bar' => 'barValue'),
                'expectedCalls' => array('doctrineHelper' => array()),
                'expectedResult' => false
            ),
            'objects_equal' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(),
                    'bar' => $right = $this->createMockObject(),
                ),
                'expectedCalls' => array(
                    'doctrineHelper' => array(
                        array('getEntityClass', array($left), '\stdClass'),
                        array('getEntityClass', array($right), '\stdClass'),
                    ),
                ),
                'expectedResult' => true
            ),
            'objects_not_equal' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(array('foo' => 'bar')),
                    'bar' => $right = $this->createMockObject(array('foo' => 'baz')),
                ),
                'expectedCalls' => array(
                    'doctrineHelper' => array(
                        array('getEntityClass', array($left), '\stdClass'),
                        array('getEntityClass', array($right), '\stdClass'),
                        array('isManageableEntity', array($left), false),
                    ),
                ),
                'expectedResult' => false
            ),
            'object_and_entity_not_equal' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(array('foo' => 'bar')),
                    'bar' => $right = $this->createMockObject(),
                ),
                'expectedCalls' => array(
                    'doctrineHelper' => array(
                        array('getEntityClass', array($left), '\stdClass'),
                        array('getEntityClass', array($right), '\stdClass'),
                        array('isManageableEntity', array($left), true),
                        array('isManageableEntity', array($right), false),
                    ),
                ),
                'expectedResult' => false
            ),
            'entities_equal' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(),
                    'bar' => $right = $this->createMockObject(),
                ),
                'expectedCalls' => array(
                    'doctrineHelper' => array(
                        array('getEntityClass', array($left), '\stdClass'),
                        array('getEntityClass', array($right), '\stdClass'),
                        array('isManageableEntity', array($left), true),
                        array('isManageableEntity', array($right), true),
                        array('getEntityIdentifier', array($left), array('id' => 1)),
                        array('getEntityIdentifier', array($right), array('id' => 1)),
                    ),
                ),
                'expectedResult' => true
            ),
            'entities_not_equal_identifiers' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(), 'bar' => $right = $this->createMockObject(),
                ),
                'expectedCalls' => array(
                    'doctrineHelper' => array(
                        array('getEntityClass', array($left), '\stdClass'),
                        array('getEntityClass', array($right), '\stdClass'),
                        array('isManageableEntity', array($left), true),
                        array('isManageableEntity', array($right), true),
                        array('getEntityIdentifier', array($left), array('id' => 1)),
                        array('getEntityIdentifier', array($right), array('id' => 2)),
                    ),
                ),
                'expectedResult' => false
            ),

        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|string $mock
     * @param array $expectedCalls
     */
    private function addMockExpectedCalls($mock, array $expectedCalls)
    {
        if (is_string($mock)) {
            $mockGetter = 'getMock' . ucfirst($mock);
            $mock = $this->$mockGetter($mock);
        }
        $index = 0;
        if ($expectedCalls) {
            foreach ($expectedCalls as $expectedCall) {
                list($method, $arguments, $result) = $expectedCall;

                if (is_callable($result)) {
                    $result = call_user_func($result);
                }

                $methodExpectation = $mock->expects($this->at($index++))->method($method);
                $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $arguments);
                $methodExpectation->will($this->returnValue($result));
            }
        } else {
            $mock->expects($this->never())->method($this->anything());
        }
    }

    /**
     * @param array $data
     * @param string $class
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockObject(array $data = array(), $class = 'stdClass')
    {
        $result = $this->getMock($class);
        foreach ($data as $property => $value) {
            $result->$property = $value;
        }
        return $result;
    }
}
