<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\Condition;

class EqualToTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $classMetadata;

    /**
     * @var Condition\EqualTo
     */
    protected $condition;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->condition = new Condition\EqualTo($this->registry, new ContextAccessor());
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
    protected function getMockRegistry()
    {
        if (!$this->registry) {
            $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        }
        return $this->registry;
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
                'expectedCalls' => array('registry' => array()),
                'expectedResult' => true
            ),
            'scalars_not_equal' => array(
                'options' => $options,
                'context' => array('foo' => 'fooValue', 'bar' => 'barValue'),
                'expectedCalls' => array('registry' => array()),
                'expectedResult' => false
            ),
            'objects_not_equal' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(),
                    'bar' => $right = $this->createMockObject(),
                ),
                'expectedCalls' => array(
                    'registry' => array(
                        array('getManagerForClass', array(get_class($left)), null),
                        array('getManagerForClass', array(get_class($right)), null),
                    ),
                ),
                'expectedResult' => true
            ),
            'objects_equal' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $this->createMockObject(array('foo' => 'bar')),
                    'bar' => $this->createMockObject(array('foo' => 'baz')),
                ),
                'expectedCalls' => array(
                    'registry' => array(
                        array('getManagerForClass', array(get_class($left)), null),
                        array('getManagerForClass', array(get_class($right)), null),
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
                    'registry' => array(
                        array('getManagerForClass', array(get_class($left)), array('self', 'getMockEntityManager')),
                        array('getManagerForClass', array(get_class($right)), null),
                    ),
                    'entityManager' => array()
                ),
                'expectedResult' => false
            ),
            'entities_equal' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(), 'bar' => $right = $this->createMockObject(),
                ),
                'expectedCalls' => array(
                    'registry' => array(
                        array('getManagerForClass', array(get_class($left)), array('self', 'getMockEntityManager')),
                        array('getManagerForClass', array(get_class($right)), array('self', 'getMockEntityManager')),
                    ),
                    'entityManager' => array(
                        array('getClassMetadata', array(get_class($left)), array('self', 'getMockClassMetadata')),
                        array('getClassMetadata', array(get_class($right)), array('self', 'getMockClassMetadata')),
                    ),
                    'classMetadata' => array(
                        array('getName', array(), 'FooEntityClass'),
                        array('getName', array(), 'FooEntityClass'),
                        array('getIdentifierValues', array($left), array(100)),
                        array('getIdentifierValues', array($right), array(100)),
                    ),
                ),
                'expectedResult' => true
            ),
            'entities_not_equal_metadata' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(), 'bar' => $right = $this->createMockObject(),
                ),
                'expectedCalls' => array(
                    'registry' => array(
                        array('getManagerForClass', array(get_class($left)), array('self', 'getMockEntityManager')),
                        array('getManagerForClass', array(get_class($right)), array('self', 'getMockEntityManager')),
                    ),
                    'entityManager' => array(
                        array('getClassMetadata', array(get_class($left)), array('self', 'getMockClassMetadata')),
                        array('getClassMetadata', array(get_class($right)), array('self', 'getMockClassMetadata')),
                    ),
                    'classMetadata' => array(
                        array('getName', array(), 'FooEntityClass'),
                        array('getName', array(), 'BarEntityClass')
                    ),
                ),
                'expectedResult' => false
            ),
            'entities_not_equal_identifiers' => array(
                'options' => $options,
                'context' => array(
                    'foo' => $left = $this->createMockObject(), 'bar' => $right = $this->createMockObject(),
                ),
                'expectedCalls' => array(
                    'registry' => array(
                        array('getManagerForClass', array(get_class($left)), array('self', 'getMockEntityManager')),
                        array('getManagerForClass', array(get_class($right)), array('self', 'getMockEntityManager')),
                    ),
                    'entityManager' => array(
                        array('getClassMetadata', array(get_class($left)), array('self', 'getMockClassMetadata')),
                        array('getClassMetadata', array(get_class($right)), array('self', 'getMockClassMetadata')),
                    ),
                    'classMetadata' => array(
                        array('getName', array(), 'FooEntityClass'),
                        array('getName', array(), 'FooEntityClass'),
                        array('getIdentifierValues', array($left), array(100)),
                        array('getIdentifierValues', array($right), array(200)),
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
     * @return ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
                ->disableOriginalConstructor()
                ->setMethods(array('getClassMetadata'))
                ->getMockForAbstractClass();
        }

        return $this->entityManager;
    }

    /**
     * @return ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockClassMetadata()
    {
        if (!$this->classMetadata) {
            $this->classMetadata = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
                ->disableOriginalConstructor()
                ->setMethods(array('getSingleIdentifierFieldName'))
                ->getMockForAbstractClass();
        }

        return $this->classMetadata;
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
