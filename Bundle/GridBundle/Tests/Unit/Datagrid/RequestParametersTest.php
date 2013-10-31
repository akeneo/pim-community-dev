<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\GridBundle\Datagrid\RequestParameters;

class RequestParametersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_ROOT_PARAMETER         = 'test_root_parameter';
    const TEST_EXISTING_PARAMETER     = 'test_existing_parameter';
    const TEST_EXISTING_VALUE         = 'test_existing_value';
    const TEST_NOT_EXISTING_PARAMETER = 'test_not_existing_parameter';
    const TEST_DEFAULT_VALUE          = 'test_default_value';
    const TEST_SCOPE                  = 'test_scope';
    const TEST_LOCALE                 = 'test_locale';

    /**
     * @var RequestParameters
     */
    protected $model;

    /**
     * @var array
     */
    protected $testParameters = array(
        self::TEST_ROOT_PARAMETER => array(
            RequestParameters::FILTER_PARAMETERS     => array('filter' => 'parameters'),
            RequestParameters::PAGER_PARAMETERS      => array('pager' => 'parameters'),
            RequestParameters::SORT_PARAMETERS       => array('sort' => 'parameters'),
            RequestParameters::ADDITIONAL_PARAMETERS => array('additional' => 'parameters'),
            RequestParameters::SCOPE_PARAMETER       => self::TEST_SCOPE,
            self::TEST_EXISTING_PARAMETER            => self::TEST_EXISTING_VALUE,
        )
    );

    protected function setUp()
    {
        $request = new Request($this->testParameters);
        $request->setLocale(self::TEST_LOCALE);

        $containerMock = $this->getMockForAbstractClass(
            'Symfony\Component\DependencyInjection\ContainerInterface',
            array(),
            '',
            false,
            true,
            true,
            array('get')
        );
        $containerMock->expects($this->any())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request));

        $this->model = new RequestParameters($containerMock, self::TEST_ROOT_PARAMETER);
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testGet()
    {
        $this->assertEquals(
            self::TEST_EXISTING_VALUE,
            $this->model->get(self::TEST_EXISTING_PARAMETER, self::TEST_DEFAULT_VALUE)
        );
        $this->assertEquals(
            self::TEST_DEFAULT_VALUE,
            $this->model->get(self::TEST_NOT_EXISTING_PARAMETER, self::TEST_DEFAULT_VALUE)
        );
    }

    public function testToArray()
    {
        // must contains only filter, pager and sort parameters
        $expectedParameters = $this->testParameters;
        unset($expectedParameters[self::TEST_ROOT_PARAMETER][self::TEST_EXISTING_PARAMETER]);
        unset($expectedParameters[self::TEST_ROOT_PARAMETER][RequestParameters::SCOPE_PARAMETER]);

        $this->assertEquals($expectedParameters, $this->model->toArray());
    }

    public function testSet()
    {
        // test set array
        $additionalParameters = array(self::TEST_EXISTING_PARAMETER => self::TEST_EXISTING_VALUE);
        $this->model->set(RequestParameters::FILTER_PARAMETERS, $additionalParameters);

        $expectedParameters = array_merge(
            $this->testParameters[self::TEST_ROOT_PARAMETER][RequestParameters::FILTER_PARAMETERS],
            $additionalParameters
        );
        $this->assertEquals($expectedParameters, $this->model->get(RequestParameters::FILTER_PARAMETERS));

        // test set scalar value
        $this->model->set(self::TEST_EXISTING_PARAMETER, self::TEST_DEFAULT_VALUE);
        $this->assertEquals(self::TEST_DEFAULT_VALUE, $this->model->get(self::TEST_EXISTING_PARAMETER));
    }

    public function testGetLocale()
    {
        $this->assertEquals(self::TEST_LOCALE, $this->model->getLocale());
    }

    public function testGetScope()
    {
        $this->assertEquals(self::TEST_SCOPE, $this->model->getScope());
    }
}
