<?php

namespace Oro\Bundle\LocaleBundle\Test\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\LocaleBundle\Test\Unit\Formatter\Stubs\PersonAllNamePartsStub;

class NameFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider formatDataProvider
     * @param string $format
     * @param string $expected
     */
    public function testFormat($format, $expected)
    {
        $provider = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())
            ->method('getNameFormat')
            ->will($this->returnValue($format));
        $formatter = new NameFormatter($provider);
        $person = new PersonAllNamePartsStub();
        $this->assertEquals($expected, $formatter->format($person));
    }

    public function formatDataProvider()
    {
        return array(
            array(
                '%last_name% %FIRST_NAME% %middle_name% %PREFIX% %suffix%',
                'ln FN mn NP ns'
            ),
            array(
                '%unknown_data_one% %last_name% %FIRST_NAME% %middle_name% %PREFIX% %suffix% %unknown_data_two%',
                'ln FN mn NP ns'
            )
        );
    }
}
