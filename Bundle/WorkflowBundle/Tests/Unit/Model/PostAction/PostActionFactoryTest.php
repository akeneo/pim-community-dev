<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;

class FilterFactoryTest extends \PHPUnit_Framework_TestCase
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
     * @return PostActionFactory
     */
    protected function buildFilterFactory($arguments = array())
    {
        $defaultArguments = array(
            'container' => $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface'),
            'types'     => $this->allowedTypes
        );
        $arguments = array_merge($defaultArguments, $arguments);

        return new PostActionFactory($arguments['container'], $arguments['types']);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The post action type must be defined
     */
    public function testCreateNoType()
    {
        $factory = $this->buildFilterFactory();
        $factory->create(null);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage No attached service to post action type named `unknown_type`
     */
    public function testCreateIncorrectType()
    {
        $factory = $this->buildFilterFactory();
        $factory->create('unknown_type');
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The service `test_type_service` must implement `PostActionInterface`
     */
    public function testCreateIncorrectInterface()
    {
        $factory = $this->buildFilterFactory();
        $factory->create(self::TEST_TYPE);
    }

    public function testCreate()
    {
        $postActionMock = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMockForAbstractClass();
        $containerMock->expects($this->once())
            ->method('get')
            ->with(self::TEST_TYPE_SERVICE)
            ->will($this->returnValue($postActionMock));

        $factory = $this->buildFilterFactory(array('container' => $containerMock));

        $this->assertEquals($postActionMock, $factory->create(self::TEST_TYPE));
    }
}
