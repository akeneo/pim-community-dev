<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\ActionFactory;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME          = 'test_name';
    const TEST_TYPE          = 'test_type';
    const TEST_TYPE_SERVICE  = 'test_type_service';
    /**#@-*/

    /**
     * @var array
     */
    protected $allowedTypes = array(
        self::TEST_TYPE => self::TEST_TYPE_SERVICE
    );

    /**
     * @param array $arguments
     * @return ActionFactory
     */
    protected function buildFilterFactory($arguments = array())
    {
        $defaultArguments = array(
            'container' => $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface'),
            'types'     => $this->allowedTypes
        );
        $arguments = array_merge($defaultArguments, $arguments);

        return new ActionFactory($arguments['container'], $arguments['types']);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The action type must be defined
     */
    public function testCreateNoType()
    {
        $factory = $this->buildFilterFactory();
        $factory->create(null);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage No attached service to action type named `unknown_type`
     */
    public function testCreateIncorrectType()
    {
        $factory = $this->buildFilterFactory();
        $factory->create('unknown_type');
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The service `test_type_service` must implement `ActionInterface`
     */
    public function testCreateIncorrectInterface()
    {
        $factory = $this->buildFilterFactory();
        $factory->create(self::TEST_TYPE);
    }

    /**
     * @param string $type
     * @param string $id
     * @param array $options
     * @param boolean $isCondition
     * @dataProvider createDataProvider
     */
    public function testCreate($type, $id, $options = array(), $isCondition = false)
    {
        $action = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $action->expects($this->once())
            ->method('initialize')
            ->with($options);

        $condition = null;
        if ($isCondition) {
            /** @var ConditionInterface $condition */
            $condition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
            $action->expects($this->once())
                ->method('setCondition')
                ->with($condition);
        } else {
            $action->expects($this->never())
                ->method('setCondition');
        }

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMockForAbstractClass();
        $container->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($action));

        $factory = $this->buildFilterFactory(array('container' => $container));

        $this->assertEquals($action, $factory->create($type, $options, $condition));
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return array(
            'empty condition' => array(
                'type' => self::TEST_TYPE,
                'id'   => self::TEST_TYPE_SERVICE,
            ),
            'existing condition' => array(
                'type'        => self::TEST_TYPE,
                'id'          => self::TEST_TYPE_SERVICE,
                'options'     => array('key' => 'value'),
                'isCondition' => true,
            ),
        );
    }
}
