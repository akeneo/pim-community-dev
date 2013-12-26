<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Route;

use Pim\Bundle\GridBundle\Route\DatagridRouteRegistry;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests DatagridRouteRegistry
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridRouteRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $cacheDir;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->cacheDir = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if ($this->cacheDir) {
            $f = new Filesystem();
            $f->remove($this->cacheDir);
        }
    }

    /**
     * @return array
     */
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
     * @param mixed   $input
     * @param mixed   $expected
     * @param boolean $cacheMode
     * @param boolean $expired
     *
     * @dataProvider getTestData
     */
    public function testGetRegexps($input, $expected, $cacheMode, $expired)
    {
        $routeCollection = $this->getMockBuilder('Symfony\Component\Routing\RouteCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollection->expects($this->any())
            ->method('getResources')
            ->will($this->returnValue(null));

        $routingContext = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->getMock();
        $routingContext->expects($this->any())
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
        if ($cacheMode) {
            $this->cacheDir = tempnam('/tmp', 'pim_tests_dategridrouteregistry');
            unlink($this->cacheDir);
            mkdir($this->cacheDir);
        }
        if ($cacheMode && !$expired) {
            $cacheFile = sprintf('%s/%s', $this->cacheDir, DatagridRouteRegistry::CACHE_FILE);
            $f = fopen($cacheFile, 'w');
            fwrite($f, sprintf('<?php return %s;', var_export($input, true)));
            fclose($f);
        } else {
            $builder->expects($this->once())
                ->method('getRegexps')
                ->will($this->returnValue($input));
        }

        $registry = new DatagridRouteRegistry($router, $builder, $this->cacheDir, false);
        $this->assertEquals($expected, $registry->getRegexps());

        //Call a second time to ensure data is cached
        $this->assertEquals($expected, $registry->getRegexps());
    }
}
