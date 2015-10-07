<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Menu\Matcher\Voter;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\NavigationBundle\Menu\Matcher\Voter\RoutePatternVoter;

class RoutePatternVoterTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchingWithoutRequest()
    {
        $item = $this->getMock('Knp\Menu\ItemInterface');
        $item->expects($this->never())->method('getExtra');

        $voter = new RoutePatternVoter();

        $this->assertNull($voter->matchItem($item));
    }

    /**
     * @param string $route
     * @param array $parameters
     * @param string|array $itemRoutes
     * @param array $itemsRoutesParameters
     * @param boolean $expected
     *
     * @dataProvider matchingDataProvider
     */
    public function testMatching($route, array $parameters, $itemRoutes, array $itemsRoutesParameters, $expected)
    {
        $item = $this->getMock('Knp\Menu\ItemInterface');
        $item->expects($this->any())
            ->method('getExtra')
            ->will(
                $this->returnValueMap(
                    array(
                        array('routes', array(), $itemRoutes),
                        array('routesParameters', array(), $itemsRoutesParameters)
                    )
                )
            );

        $request = new Request();
        $request->attributes->set('_route', $route);
        foreach ($parameters as $name => $value) {
            $request->attributes->set($name, $value);
        }

        $voter = new RoutePatternVoter();
        $voter->setRequest($request);

        $this->assertSame($expected, $voter->matchItem($item));
    }

    public function matchingDataProvider()
    {
        return array(
            'no request route' => array(null, array(), 'foo', array(), null),
            'no item route' => array('foo', array(), null, array(), null),
            'same single route' => array('foo', array(), 'foo', array(), true),
            'different single route' => array('foo', array(), 'bar', array(), null),
            'matching mutiple routes' => array('foo', array(), array('foo', 'baz'), array(), true),
            'matching mutiple routes 2' => array('baz', array(), array('foo', 'baz'), array(), true),
            'different multiple routes' => array('foo', array(), array('bar', 'baz'), array(), null),
            'same single route with different parameters' => array(
                'foo', array('1' => 'bar'),
                'foo', array('foo' => array('1' => 'baz')),
                null
            ),
            'same single route with same parameters' => array(
                'foo', array('1' => 'bar'),
                'foo', array('foo' => array('1' => 'bar')),
                true
            ),
            'same single route with additional parameters' => array(
                'foo', array('1' => 'bar'),
                'foo', array('foo' => array('1' => 'bar', '2' => 'baz')),
                null
            ),
            'same single route with less parameters' => array(
                'foo', array('1' => 'bar', '2' => 'baz'),
                'foo', array('foo' => array('1' => 'bar')),
                true
            ),
            'same single route with same type parameters' => array(
                'foo', array('1' => 2),
                'foo', array('foo' => array('1' => 2)),
                true
            ),
            'same single route with different type parameters' => array(
                'foo', array('1' => 2),
                'foo', array('foo' => array('1' => '2')),
                true
            ),
            'match regex pattern' => array('foo', array(), '/^foo$/', array(), true),
            'not match regex pattern' => array('foo', array(), '/bar/', array(), null),
            'match wildcard' => array('foo', array(), 'fo*', array(), true),
            'not match wildcard' => array('foo', array(), 'ba*', array(), null),
        );
    }
}
