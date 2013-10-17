<?php

namespace Oro\Bundle\LocaleBundle\Test\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs\PersonAllNamePartsStub;
use Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs\PersonFullNameStub;

class NameFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider formatDataProvider
     * @param string $format
     * @param string $expected
     * @param object $person
     */
    public function testFormat($format, $expected, $person)
    {
        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->getMock();
        $localeSettings->expects($this->once())
            ->method('getNameFormat')
            ->will($this->returnValue($format));
        $formatter = new NameFormatter($localeSettings);
        $this->assertEquals($expected, $formatter->format($person));
    }

    public function formatDataProvider()
    {
        return array(
            array(
                '%last_name% %FIRST_NAME% %middle_name% %PREFIX% %suffix%',
                'ln FN mn NP ns',
                new PersonAllNamePartsStub()
            ),
            array(
                '%unknown_data_one% %last_name% %FIRST_NAME% %middle_name% %PREFIX% %suffix% %unknown_data_two%',
                'ln FN mn NP ns',
                new PersonFullNameStub()
            )
        );
    }
}
