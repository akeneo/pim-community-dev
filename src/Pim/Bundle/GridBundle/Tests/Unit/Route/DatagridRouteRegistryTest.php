<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Route;

use Pim\Bundle\GridBundle\Route\DatagridRouteRegistry;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of DatagridRouteRegistryTest
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class DatagridRouteRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $cacheDir;

    public function setUp()
    {
        $this->cacheDir = null;
    }

    public function tearDown()
    {
        if ($this->cacheDir) {
            $f = new Filesystem;
            $f->remove($this->cacheDir);
        }
    }

    public function getTestData()
    {
        return array(
            'no_cache' => array(
                array('/aa%prefix%bb/', '/bbcc/', '%prefixaaa'),
                array('/aa\\/base_url\\/bb/', '/bbcc/', '%prefixaaa'),
                false,
                false
            ),
            'cache' => array(
                array('/aa%prefix%bb/'),
                array('/aa\\/base_url\\/bb/'),
                true,
                false
            ),
            'expired_cache' => array(
                array('/aa%prefix%bb/'),
                array('/aa\\/base_url\\/bb/'),
                true,
                true
            ),
        );
    }

    /**
     * @dataProvider getTestData
     */
    public function testGetRegexps($input, $expected, $cacheMode, $expired)
    {
        $routeCollection = $this->getMockBuilder('Symfony\Component\Routing\RouteCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollection->expects($this->any())
            ->method('getResources')
            ->will($this->returnValue(NULL));

        $routingContext = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->getMock();
        $routingContext->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('/base_url/'));

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())
                ->method('getRouteCollection')
                ->will($this->returnValue($routeCollection));
        $router->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($routingContext));

        $builder = $this->getMockBuilder('Pim\Bundle\GridBundle\Route\DatagridRouteRegistryBuilder')
                ->disableOriginalConstructor()
                ->getMock();
        $builder->expects($this->any())
                ->method('getRegexps')
                ->will($this->returnValue($input));

        if ($cacheMode && !$expired) {
            $this->cacheDir = tempnam('/tmp', 'pim_tests_dategridrouteregistry');
            unlink($this->cacheDir);
            mkdir($this->cacheDir);
            $cacheFile = sprintf('%s/%s', $this->cacheDir, DatagridRouteRegistry::CACHE_FILE);
            $f = fopen($cacheFile, 'w');
            fwrite($f, sprintf('<?php return %s;', var_export($input, true)));
            fclose($f);
        }

        $registry = new DatagridRouteRegistry($router, $builder, $this->cacheDir, TRUE);
        $this->assertEquals($expected, $registry->getRegexps());
    }

}
