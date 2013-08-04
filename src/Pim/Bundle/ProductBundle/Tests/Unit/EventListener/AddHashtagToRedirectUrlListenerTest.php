<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\EventListener;

use Pim\Bundle\ProductBundle\EventListener\AddHashtagToRedirectedUrlListener;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddHashtagToRedirectUrlListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $listener;

    public function setUp()
    {
        $this->listener = new AddHashtagToRedirectedUrlListener;
    }

    public function testAppendHashtag()
    {
        $request  = $this->getRequestMock(array('hash' => 'top'));
        $response = $this->getRedirectResponseMock('http://over.the-world.com/home');
        $event    = $this->getEventMock($request, $response);

        $response->expects($this->once())
            ->method('setTargetUrl')
            ->with('http://over.the-world.com/home#top');

        $this->listener->onKernelResponse($event);
    }

    public function testDoNotAppendHashtag()
    {
        $request  = $this->getRequestMock();
        $response = $this->getRedirectResponseMock('http://over.the-world.com/home');
        $event    = $this->getEventMock($request, $response);

        $response->expects($this->never())
            ->method('setTargetUrl');

        $this->listener->onKernelResponse($event);
    }

    public function testSupportOnlyRedirectResponse()
    {
        $request  = $this->getRequestMock();
        $response = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $event    = $this->getEventMock($request, $response);

        $response->expects($this->never())
            ->method('setTargetUrl');

        $this->listener->onKernelResponse($event);
    }

    private function getEventMock($request, $response)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        return $event;
    }

    private function getRequestMock(array $parameters = array())
    {
        $request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $bag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($parameters as $key => $value) {
            $bag->expects($this->any())
                ->method('has')
                ->with($key)
                ->will($this->returnValue(true));

            $bag->expects($this->any())
                ->method('get')
                ->with($key)
                ->will($this->returnValue($value));
        }
        $request->query = $bag;

        return $request;
    }

    private function getRedirectResponseMock($targetUrl)
    {
        $response = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\RedirectResponse')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->any())
            ->method('getTargetUrl')
            ->will($this->returnValue($targetUrl));

        return $response;
    }
}
