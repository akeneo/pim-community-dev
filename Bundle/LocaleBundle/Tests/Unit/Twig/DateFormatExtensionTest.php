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
}
