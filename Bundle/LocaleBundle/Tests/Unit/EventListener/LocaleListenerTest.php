<?php

namespace Oro\Bundle\LocaleBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\LocaleBundle\EventListener\LocaleListener;

class LocaleListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocaleListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var string
     */
    protected $defaultLocale;

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new LocaleListener($this->localeSettings);
        $this->defaultLocale = \Locale::getDefault();
    }

    protected function tearDown()
    {
        \Locale::setDefault($this->defaultLocale);
    }

    public function testOnKernelRequest()
    {
        $request = new Request();

        $language = 'ru';
        $locale = 'fr';

        $this->localeSettings->expects($this->once())->method('getLanguage')
            ->will($this->returnValue($language));

        $this->localeSettings->expects($this->once())->method('getLocale')
            ->will($this->returnValue($locale));

        $this->listener->onKernelRequest($this->createGetResponseEvent($request));

        $this->assertEquals($language, $request->getLocale());
        $this->assertEquals($locale, \Locale::getDefault());
    }

    protected function createGetResponseEvent(Request $request)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequest'))
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $event;
    }
}
