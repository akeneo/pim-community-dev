<?php
namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;

class ConditionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_TYPE = 'test_condition';
    const TEST_TYPE_SERVICE = 'test_condition_service';
    /**#@-*/

    /**
     * @var ConditionFactory
     */
    protected $model;

    /**
     * @var array
     */
    protected $allowedTypes = array(
        self::TEST_TYPE => self::TEST_TYPE_SERVICE
    );

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    protected function tearDown()
    {
        unset($this->model);
    }

    protected function setUp()
    {
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->model = new ConditionFactory($this->container, $this->allowedTypes);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The type must be defined
     */
    public function testCreateNoType()
    {
        $this->model->create(null, array());
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage No attached service to condition type named `unknown`
     */
    public function testCreateIncorrectType()
    {
        $this->model->create('unknown');
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The service `test_condition_service` must implement `ConditionInterface`
     */
    public function testCreateIncorrectInterface()
    {
        $this->model->create(self::TEST_TYPE);
    }

    public function testCreate()
    {
        $options = array('key' => 'value');
        $message = 'Test';
        $conditionMock = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->getMock();

        $conditionMock->expects($this->once())
            ->method('setMessage')
            ->with($message);

        $conditionMock->expects($this->once())
            ->method('initialize')
            ->with($options);

        $this->container->expects($this->once())
            ->method('get')
            ->with(self::TEST_TYPE_SERVICE)
            ->will($this->returnValue($conditionMock));

        $this->model->create(self::TEST_TYPE, $options, $message);
    }

    /**
     * @return array
     */
    public function optionsDataProvider()
    {
        return array(
            array(),
        );
    }
}
