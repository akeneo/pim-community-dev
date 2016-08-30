<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Event;

use Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseHashnavListenerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_URL = 'http://test_url/';
    const TEMPLATE = 'OroNavigationBundle:HashNav:redirect.html.twig';

    /**
     * @var \Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener
     */
    protected $listener;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    protected $templating;
    protected $event;
    protected $tokenStorage;

    public function setUp()
    {
        $this->response = new Response();
        $this->request = Request::create(self::TEST_URL);
        $this->request->headers->add([ResponseHashnavListener::HASH_NAVIGATION_HEADER => true]);
        $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->response));

        $this->tokenStorage = $this->getMock('Symfony\Component\Security\Core\TokenStorageInterface');
        $this->templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $this->listener = new ResponseHashnavListener($this->tokenStorage, $this->templating);
    }

    public function testPlainRequest()
    {
        $testBody = 'test';
        $this->response->setContent($testBody);

        $this->listener->onResponse($this->event);

        $this->assertEquals($testBody, $this->response->getContent());
    }

    public function testHashRequestWOUser()
    {
        $this->response->setStatusCode(302);
        $this->response->headers->add(['location' => self::TEST_URL]);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(false));

        $this->event->expects($this->once())
            ->method('setResponse');

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                self::TEMPLATE,
                [
                    'full_redirect' => true,
                    'location'      => self::TEST_URL
                ]
            )
            ->will($this->returnValue(new Response()));

        $this->listener->onResponse($this->event);
    }

    public function testHashRequestNotFound()
    {
        $this->response->setStatusCode(404);
        $this->serverErrorHandle();
    }

    private function serverErrorHandle()
    {
        $this->event->expects($this->once())
            ->method('setResponse');

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                self::TEMPLATE,
                [
                    'full_redirect' => true,
                    'location'      => self::TEST_URL
                ]
            )
            ->will($this->returnValue(new Response()));

        $this->listener->onResponse($this->event);
    }
}
