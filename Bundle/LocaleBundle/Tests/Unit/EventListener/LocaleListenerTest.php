<?php

namespace Oro\Bundle\LocaleBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

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

        $this->listener = new LocaleListener($this->localeSettings, true);
        $this->defaultLocale = \Locale::getDefault();
    }

    protected function tearDown()
    {
        \Locale::setDefault($this->defaultLocale);
    }

    /**
     * @param mixed $installed
     * @param bool $isSetLocale
     * @dataProvider onKernelRequestDataProvider
     */
    public function testOnKernelRequest($installed, $isSetLocale)
    {
        $customLanguage = 'ru';
        $customLocale = 'fr';

        $request = new Request();
        $request->setDefaultLocale($this->defaultLocale);

        if ($isSetLocale) {
            $this->localeSettings->expects($this->once())->method('getLanguage')
                ->will($this->returnValue($customLanguage));
            $this->localeSettings->expects($this->once())->method('getLocale')
                ->will($this->returnValue($customLocale));
        } else {
            $this->localeSettings->expects($this->never())->method('getLanguage');
            $this->localeSettings->expects($this->never())->method('getLocale');
        }

        $this->listener = new LocaleListener($this->localeSettings, $installed);
        $this->listener->onKernelRequest($this->createGetResponseEvent($request));

        if ($isSetLocale) {
            $this->assertEquals($customLanguage, $request->getLocale());
            $this->assertEquals($customLocale, \Locale::getDefault());
        } else {
            $this->assertEquals($this->defaultLocale, $request->getLocale());
            $this->assertEquals($this->defaultLocale, \Locale::getDefault());
        }
    }

    public function onKernelRequestDataProvider()
    {
        return array(
            'application not installed with null' => array(
                'installed' => null,
                'isSetLocale' => false,
            ),
            'application not installed with false' => array(
                'installed' => false,
                'isSetLocale' => false,
            ),
            'application installed with flag' => array(
                'installed' => true,
                'isSetLocale' => true,
            ),
            'application installed with date' => array(
                'installed' => '2012-12-12T12:12:12+02:00',
                'isSetLocale' => true,
            ),
        );
    }

    /**
     * @param Request $request
     * @return GetResponseEvent
     */
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
