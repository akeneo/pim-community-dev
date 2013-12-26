<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Route;

use Pim\Bundle\GridBundle\Route\DatagridRouteRegistryBuilder;

/**
 * Tests DatagridRouteRegistryBuilder
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridRouteRegistryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestData()
    {
        return array(
            'without_errors' => array(
                array('grid1' => 'url1', 'grid2' => 'url2'),
                array('grid1' => '/url1/', 'grid2' => '/url2/'),
            ),
            'with_errors' => array(
                array('grid1' => 'url1', 'grid2' => 'url2', 'grid3'=>'++'),
                array('grid1' => '/url1/', 'grid2' => '/url2/'),
            ),
        );
    }

    /**
     * @param array $input
     * @param array $expected
     *
     * @dataProvider getTestData
     */
    public function testGetRegexps($input, $expected)
    {
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $routeCollection = $this->getMockBuilder('Symfony\Component\Routing\RouteCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $router->expects($this->once())
            ->method('getRouteCollection')
            ->will($this->returnValue($routeCollection));

        $routeCollectionGetValueMap = array();

        $builder = new DatagridRouteRegistryBuilder($router);
        foreach ($input as $datagridName => $routeRegexp) {
            $routeName = 'route_' . $datagridName;
            $builder->addRoute($datagridName, $routeName);

            $route = $this->getMockBuilder('Symfony\Component\Routing\Route')
                    ->disableOriginalConstructor()
                    ->getMock();
            $compiledRoute = $this->getMockBuilder('Symfony\Component\Routing\CompiledRoute')
                    ->disableOriginalConstructor()
                    ->getMock();
            $route->expects($this->once())
                ->method('compile')
                ->will($this->returnValue($compiledRoute));
            $compiledRoute->expects($this->once())
                ->method('getRegex')
                ->will($this->returnValue($routeRegexp));

            $routeCollectionGetValueMap[] = array($routeName, $route);
        }

        $routeCollection->expects($this->exactly(count($input)))
            ->method('get')
            ->will($this->returnValueMap($routeCollectionGetValueMap));

        $this->assertEquals($expected, $builder->getRegexps());
    }
}
