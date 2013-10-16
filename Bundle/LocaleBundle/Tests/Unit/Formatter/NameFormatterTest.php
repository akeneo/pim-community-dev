<?php

namespace Oro\Bundle\LocaleBundle\Test\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\LocaleBundle\Test\Unit\Formatter\Stubs\PersonAllNamePartsStub;

class NameFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $format = '%last_name% %FIRST_NAME% %middle_name% %PREFIX% %suffix%';
        $provider = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())
            ->method('getNameFormat')
            ->will($this->returnValue($format));
        $formatter = new NameFormatter($provider);
        $person = new PersonAllNamePartsStub();
        $this->assertEquals('ln FN mn NP ns', $formatter->format($person));
    }
}
