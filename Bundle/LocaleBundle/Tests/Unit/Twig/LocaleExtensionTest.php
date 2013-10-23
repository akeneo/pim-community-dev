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

    /**
     * @var array
     */
    protected $expectedFunctions = array(
        'oro_locale' => 'getLocale',
        'oro_timezone_offset' => 'getTimeZoneOffset',
    );

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
        $actualFunctions = $this->extension->getFunctions();
        $this->assertSameSize($this->expectedFunctions, $actualFunctions);

        /** @var $actualFunction \Twig_SimpleFunction */
        foreach ($actualFunctions as $actualFunction) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $actualFunction);
            $actualFunctionName = $actualFunction->getName();
            $this->assertArrayHasKey($actualFunctionName, $this->expectedFunctions);
            $expectedCallback = array($this->extension, $this->expectedFunctions[$actualFunctionName]);
            $this->assertEquals($expectedCallback, $actualFunction->getCallable());
        }
    }

    public function testGetLocale()
    {
        $locale = 'en_US';

        $this->localeSettings->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        $this->assertEquals($locale, $this->extension->getLocale());
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
