<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionFactory;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME          = 'test_name';
    const TEST_TYPE          = 'test_type';
    const TEST_TYPE_SERVICE  = 'test_type_service';
    const TEST_ACL_RESOURCE  = 'test_acl_resource';

    /**
     * @var ActionFactory
     */
    protected $model;

    /**
     * @var array
     */
    protected $allowedTypes = array(
        self::TEST_TYPE => self::TEST_TYPE_SERVICE
    );

    /**
     * @var array
     */
    protected $testOptions = array('key' => 'value');

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @param array $arguments
     */
    protected function initializeActionFactory($arguments = array())
    {
        $defaultArguments = array(
            'container' => $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface'),
            'types'     => $this->allowedTypes
        );

        $arguments =  array_merge($defaultArguments, $arguments);

        $this->model = new ActionFactory($arguments['container'], $arguments['types']);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The type must be defined
     */
    public function testCreateNoType()
    {
        $this->initializeActionFactory();

        $this->model->create(self::TEST_NAME, null);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage No attached service to action type named "unknown_type"
     */
    public function testCreateIncorrectType()
    {
        $this->initializeActionFactory();

        $this->model->create(self::TEST_NAME, 'unknown_type');
    }

    public function testCreate()
    {
        $actionMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Action\AbstractAction', array(), '', false);

        $containerMock = $this->getMockForAbstractClass(
            'Symfony\Component\DependencyInjection\ContainerInterface',
            array(),
            '',
            false,
            true,
            true,
            array('get')
        );
        $containerMock->expects($this->once())
            ->method('get')
            ->with(self::TEST_TYPE_SERVICE)
            ->will($this->returnValue($actionMock));

        $this->initializeActionFactory(array('container' => $containerMock));

        $action = $this->model->create(self::TEST_NAME, self::TEST_TYPE, self::TEST_ACL_RESOURCE, $this->testOptions);

        $this->assertEquals($actionMock, $action);
        $this->assertEquals(self::TEST_NAME, $action->getName());
        $this->assertEquals(self::TEST_ACL_RESOURCE, $action->getAclResource());
        $this->assertAttributeEquals($this->testOptions, 'options', $action);
    }
}
