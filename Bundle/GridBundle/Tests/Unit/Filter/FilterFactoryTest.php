<?php
namespace Oro\Bundle\GridBundle\Tests\Unit\Filter;

use Oro\Bundle\GridBundle\Filter\FilterFactory;

class FilterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME          = 'test_name';
    const TEST_TYPE          = 'test_type';
    const TEST_TYPE_SERVICE  = 'test_type_service';
    const TEST_ACL_RESOURCE  = 'test_acl_resource';
    /**#@-*/

    /**
     * @var FilterFactory
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
    protected function initializeFilterFactory($arguments = array())
    {
        $defaultArguments = array(
            'container' => $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface'),
            'types'     => $this->allowedTypes
        );

        $arguments =  array_merge($defaultArguments, $arguments);

        $this->model = new FilterFactory($arguments['container'], $arguments['types']);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The type must be defined
     */
    public function testCreateNoType()
    {
        $this->initializeFilterFactory();

        $this->model->create(self::TEST_NAME, null);
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage No attached service to type named `unknown_type`
     */
    public function testCreateIncorrectType()
    {
        $this->initializeFilterFactory();

        $this->model->create(self::TEST_NAME, 'unknown_type');
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The service `test_type_service` must implement `FilterInterface`
     */
    public function testCreateIncorrectInterface()
    {
        $this->initializeFilterFactory();

        $this->model->create(self::TEST_NAME, self::TEST_TYPE);
    }

    public function testCreate()
    {
        $filterMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\FilterInterface',
            array(),
            '',
            false,
            true,
            true,
            array('initialize')
        );
        $filterMock->expects($this->once())
            ->method('initialize')
            ->with(self::TEST_NAME, $this->testOptions);

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
            ->will($this->returnValue($filterMock));

        $this->initializeFilterFactory(array('container' => $containerMock));

        $this->model->create(self::TEST_NAME, self::TEST_TYPE, $this->testOptions);
    }
}
