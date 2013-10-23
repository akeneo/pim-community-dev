<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Twig;

use Oro\Bundle\LocaleBundle\Twig\LocaleExtension;

class LocaleExtensionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TYPE = 'test_format_type';
    const TEST_FORMAT = 'MMM, d y t';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var LocaleExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->localeSettings =
            $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
                ->disableOriginalConstructor()
                ->setMethods(array('getLocale', 'getTimeZone'))
                ->getMock();

        $this->extension = new LocaleExtension($this->localeSettings);
    }

    protected function tearDown()
    {
        unset($this->localeSettings);
        unset($this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_locale', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $expectedFunctions = array(
            'oro_locale' => array($this->localeSettings, 'getLocale'),
            'oro_language' => array($this->localeSettings, 'getLanguage'),
            'oro_country' => array($this->localeSettings, 'getCountry'),
            'oro_currency' => array($this->localeSettings, 'getCurrency'),
            'oro_timezone' => array($this->localeSettings, 'getTimeZone'),
            'oro_timezone_offset' => array($this->extension, 'getTimeZoneOffset'),
            'oro_format_address_by_address_country' => array(
                $this->localeSettings,
                'isFormatAddressByAddressCountry'
            )
        );

        $actualFunctions = $this->extension->getFunctions();
        $this->assertSameSize($expectedFunctions, $actualFunctions);

        /** @var $actualFunction \Twig_SimpleFunction */
        foreach ($actualFunctions as $actualFunction) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $actualFunction);
            $actualFunctionName = $actualFunction->getName();
            $this->assertArrayHasKey($actualFunctionName, $expectedFunctions);
            $this->assertEquals($expectedFunctions[$actualFunctionName], $actualFunction->getCallable());
        }
    }

    public function testGetTimeZoneOffset()
    {
        $timezoneString = 'UTC';
        $timezoneOffset = '+00:00';

        $this->localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->will($this->returnValue($timezoneString));

        $this->assertEquals($timezoneOffset, $this->extension->getTimeZoneOffset());
    }
}
