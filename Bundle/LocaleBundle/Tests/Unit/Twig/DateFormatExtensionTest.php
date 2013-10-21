<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Model;

use Oro\Bundle\LocaleBundle\Twig\DateFormatExtension;
use Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterRegistry;

class DateFormatExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected $converterRegistry;

    /**
     * @var DateFormatExtension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $expectedFunctions = array(
        'oro_dateformat' => 'getDateFormat',
        'oro_timeformat' => 'getTimeFormat',
        'oro_datetimeformat' => 'getDateTimeFormat',
    );

    protected function setUp()
    {
        $this->converterRegistry =
            $this->getMockBuilder('Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterRegistry')
                ->disableOriginalConstructor()
                ->setMethods(array())
                ->getMock();

        $this->extension = new DateFormatExtension($this->converterRegistry);
    }

    protected function tearDown()
    {
        unset($this->converterRegistry);
        unset($this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_locale_dateformat', $this->extension->getName());
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
}
