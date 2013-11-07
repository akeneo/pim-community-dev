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

    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     */
    public function testCreate(array $options)
    {
        $initOptions = $options;
        $conditionMock = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->getMock();
        if (isset($options['message'])) {
            unset($initOptions['message']);

            $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
                ->getMockForAbstractClass();
            $translatedMessage = 'Translated message';
            $translator->expects($this->once())
                ->method('trans')
                ->with($options['message'])
                ->will($this->returnValue($translatedMessage));

            $this->container->expects($this->at(1))
                ->method('get')
                ->with('translator')
                ->will($this->returnValue($translator));
            $conditionMock->expects($this->once())
                ->method('setMessage')
                ->with($translatedMessage);
        }
        if (isset($options['rules'])) {
            $initOptions = $options['rules'];
        }
        $conditionMock->expects($this->once())
            ->method('initialize')
            ->with($initOptions);

        $this->container->expects($this->at(0))
            ->method('get')
            ->with(self::TEST_TYPE_SERVICE)
            ->will($this->returnValue($conditionMock));

        $this->model->create(self::TEST_TYPE, $options);
    }

    /**
     * @return array
     */
    public function optionsDataProvider()
    {
        return array(
            array(array('key' => 'value')),
            array(array('key' => 'value', 'message' => 'Test')),
            array(array('key', 'value', 'message' => 'Test')),
            array(array('rules' => array('key', 'value'), 'message' => 'Test')),
        );
    }
}
