<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Route;

use Symfony\Component\Routing\RouterInterface;
use Oro\Bundle\GridBundle\Route\DefaultRouteGenerator;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;

class DefaultRouteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_ROUTE_NAME     = 'test_route_name';
    const TEST_ROOT_PARAMETER = 'test_root_parameter';
    const TEST_URL            = 'test_url';
    const TEST_FIELD_NAME     = 'test_field_name';
    const TEST_SORT_DIRECTION = 'test_sort_direction';
    const TEST_PAGE           = 12;
    const TEST_PER_PAGE       = 42;

    /**
     * @var DefaultRouteGenerator
     */
    protected $model;

    /**
     * @var array
     */
    protected $testBasicParameters = array(self::TEST_ROOT_PARAMETER => array('basic' => 'parameter'));

    /**
     * @var array
     */
    protected $testExtendParameters = array(self::TEST_ROOT_PARAMETER => array('extended' => 'parameter'));

    /**
     * @var array
     */
    protected $testRouteParameters = array('id' => 1);

    /**
     * @var array
     */
    protected $expectedSortParameters = array(
        self::TEST_ROOT_PARAMETER => array(
            ParametersInterface::SORT_PARAMETERS => array(
                self::TEST_FIELD_NAME => self::TEST_SORT_DIRECTION
            )
        )
    );

    /**
     * @var array
     */
    protected $expectedPagerParameters = array(
        self::TEST_ROOT_PARAMETER => array(
            ParametersInterface::PAGER_PARAMETERS => array(
                '_page'     => self::TEST_PAGE,
                '_per_page' => self::TEST_PER_PAGE,
            )
        )
    );

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * Data provider for testGenerateUrl
     *
     * @return array
     */
    public function generateUrlDataProvider()
    {
        return array(
            'with_basic_parameters' => array(
                '$expectedParameters' => array_merge_recursive($this->testBasicParameters, $this->testExtendParameters),
                '$parameters'         => $this->testBasicParameters,
                '$extendParameters'   => $this->testExtendParameters,
            ),
            'without_basic_parameters' => array(
                '$expectedParameters' => $this->testExtendParameters,
                '$parameters'         => array(),
                '$extendParameters'   => $this->testExtendParameters,
            ),
        );
    }

    /**
     * @param array $parameters
     * @return ParametersInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getParametersMock(array $parameters = array())
    {
        $parametersMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ParametersInterface',
            array(),
            '',
            false,
            true,
            true,
            array('toArray')
        );
        $parametersMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($parameters));

        return $parametersMock;
    }

    /**
     * @param array $parameters
     * @return RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRouterMock($parameters = array())
    {
        $routerMock = $this->getMockForAbstractClass(
            'Symfony\Component\Routing\RouterInterface',
            array(),
            '',
            false,
            true,
            true,
            array('generate')
        );
        $routerMock->expects($this->once())
            ->method('generate')
            ->with(self::TEST_ROUTE_NAME, $parameters)
            ->will($this->returnValue(self::TEST_URL));

        return $routerMock;
    }

    /**
     * @param array $expectedParameters
     * @param array $parameters
     * @param array $extendParameters
     *
     * @dataProvider generateUrlDataProvider
     */
    public function testGenerateUrl(array $expectedParameters, array $parameters, array $extendParameters)
    {
        $parametersMock = null;
        if ($parameters) {
            $parametersMock = $this->getParametersMock($parameters);
        }

        $routerMock = $this->getRouterMock(array_merge($expectedParameters, $this->testRouteParameters));

        $this->model = new DefaultRouteGenerator($routerMock, self::TEST_ROUTE_NAME);
        $this->model->setRouteParameters($this->testRouteParameters);
        $this->assertEquals(
            self::TEST_URL,
            $this->model->generateUrl($parametersMock, $extendParameters)
        );
    }

    public function testGenerateSortUrl()
    {
        $expectedParameters = array_merge_recursive($this->testBasicParameters, $this->expectedSortParameters);
        $parametersMock = $this->getParametersMock($this->testBasicParameters);
        $routerMock = $this->getRouterMock(array_merge($expectedParameters, $this->testRouteParameters));

        $field = new FieldDescription();
        $field->setName(self::TEST_FIELD_NAME);

        $this->model = new DefaultRouteGenerator($routerMock, self::TEST_ROUTE_NAME);
        $this->model->setRouteParameters($this->testRouteParameters);
        $this->assertEquals(
            self::TEST_URL,
            $this->model->generateSortUrl($parametersMock, $field, self::TEST_SORT_DIRECTION)
        );
    }

    /**
     * Data provider for testGeneratePagerUrl
     *
     * @return array
     */
    public function generatePagerUrlDataProvider()
    {
        $parametersWithPerPage = array_merge_recursive($this->testBasicParameters, $this->expectedPagerParameters);

        $parametersWithoutPerPage = $parametersWithPerPage;
        unset($parametersWithoutPerPage[self::TEST_ROOT_PARAMETER][ParametersInterface::PAGER_PARAMETERS]['_per_page']);

        return array(
            'with_per_page' => array(
                '$expectedParameters' => $parametersWithPerPage,
                '$parameters'         => $this->testBasicParameters,
                '$page'               => self::TEST_PAGE,
                '$perPage'            => self::TEST_PER_PAGE,
            ),
            'without_per_page' => array(
                '$expectedParameters' => $parametersWithoutPerPage,
                '$parameters'         => $this->testBasicParameters,
                '$page'               => self::TEST_PAGE,
            ),
        );
    }

    /**
     * @param array $expectedParameters
     * @param array $parameters
     * @param int $page
     * @param int|null $perPage
     *
     * @dataProvider generatePagerUrlDataProvider
     */
    public function testGeneratePagerUrl(array $expectedParameters, array $parameters, $page, $perPage = null)
    {
        $parametersMock = $this->getParametersMock($parameters);
        $routerMock = $this->getRouterMock(array_merge($expectedParameters, $this->testRouteParameters));
        $this->model = new DefaultRouteGenerator($routerMock, self::TEST_ROUTE_NAME);
        $this->model->setRouteParameters($this->testRouteParameters);
        $this->assertEquals(
            self::TEST_URL,
            $this->model->generatePagerUrl($parametersMock, $page, $perPage)
        );
    }

    public function testSetRouteParameters()
    {
        $routerMock = $this->getMockForAbstractClass('Symfony\Component\Routing\RouterInterface');
        $this->model = new DefaultRouteGenerator($routerMock, self::TEST_ROUTE_NAME);
        $this->model->setRouteParameters($this->testRouteParameters);
        $this->assertAttributeEquals($this->testRouteParameters, 'routeParameters', $this->model);
    }
}
