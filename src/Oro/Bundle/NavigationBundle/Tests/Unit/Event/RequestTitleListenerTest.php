<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Event;

use Oro\Bundle\NavigationBundle\Event\RequestTitleListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestTitleListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleService;

    public function setUp()
    {
        $this->titleService = $this->getMock('Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface');
    }

    /**
     * @dataProvider provider
     * @param $data
     */
    public function testRequest($data)
    {
        $invokeTimes = (int) ($data == HttpKernelInterface::MASTER_REQUEST);

        /** @var $request \PHPUnit_Framework_MockObject_MockObject */
        $request = $this->getRequest($invokeTimes);

        $titleService = $this->titleService;

        if ($invokeTimes) {
            $titleService->expects($this->exactly($invokeTimes))
                ->method('loadByRoute')
                ->with('test_route');
        }

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue($data));

        $listener = new RequestTitleListener($titleService);
        $listener->onKernelRequest($event);
    }

    /**
     * @return array
     */
    public function provider()
    {
        return array(
            array(HttpKernelInterface::MASTER_REQUEST),
            array(HttpKernelInterface::SUB_REQUEST)
        );
    }

    /**
     * Creates request mock object
     *
     * @param  int     $invokeTimes
     * @return Request
     */
    private function getRequest($invokeTimes)
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->exactly($invokeTimes))
            ->method('getRequestFormat')
            ->will($this->returnValue('html'));

        $request->expects($this->exactly($invokeTimes))
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $request->expects($this->exactly($invokeTimes))
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(false));

        $invokationMoker = $request->expects($this->exactly($invokeTimes))
            ->method('get')
            ->will($this->returnValue('test_route'));

        // used this trick due to bug in phpUnit
        // https://github.com/sebastianbergmann/phpunit/issues/270
        if ($invokeTimes) {
            $invokationMoker->with('_route');
        }

        return $request;
    }
}
