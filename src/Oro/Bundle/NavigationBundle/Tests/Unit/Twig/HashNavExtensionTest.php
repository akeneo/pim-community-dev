<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Twig;

use Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener;
use Oro\Bundle\NavigationBundle\Twig\HashNavExtension;

class HashNavExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\NavigationBundle\Twig\HashNavExtension
     */
    private $extension;

    public function setUp()
    {
        $this->extension = new HashNavExtension();
    }

    public function testCheckIsHashNavigation()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(\Symfony\Component\HttpKernel\HttpKernel::MASTER_REQUEST));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $request->headers = $this->getMockBuilder('\Symfony\Component\HttpFoundation\HeaderBag')
            ->disableOriginalConstructor()
            ->getMock();

        $request->headers->expects($this->once())
            ->method('get')
            ->will($this->returnValue(false));

        $request->expects($this->once())
            ->method('get')
            ->will($this->returnValue(true));

        $this->extension->onKernelRequest($event);

        $this->assertTrue($this->extension->checkIsHashNavigation());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertTrue(is_array($functions));
        $this->assertTrue(array_key_exists('oro_is_hash_navigation', $functions));
        $this->assertTrue(array_key_exists('oro_hash_navigation_header', $functions));
    }

    public function testGetHashNavigationHeaderConst()
    {
        $this->assertEquals(
            $this->extension->getHashNavigationHeaderConst(),
            ResponseHashnavListener::HASH_NAVIGATION_HEADER
        );
    }

    public function testGetName()
    {
        $this->assertEquals('oro_hash_nav', $this->extension->getName());
    }
}
