<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;
use Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs\AddressStub;

class AddressFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider formatDataProvider
     * @param string $format
     * @param string $expected
     */
    public function testFormat($format, $expected)
    {
        $address = new AddressStub();

        $provider = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())
            ->method('getAddressFormat')
            ->will($this->returnValue($format));
        $nameFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NameFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $nameFormatter->expects($this->once())
            ->method('format')
            ->with($address)
            ->will($this->returnValue('Formatted User NAME'));

        $formatter = new AddressFormatter($provider, $nameFormatter);
        $this->assertEquals($expected, $formatter->format($address));
    }

    public function formatDataProvider()
    {
        return array(
            array(
                '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str. apartment 10\nNEW YORK NY UNITED STATES 12345"
            ),
            array(
                '%name%\n%organization%\n%street1%\n%street2%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str.\napartment 10\nNEW YORK NY UNITED STATES 12345"
            ),
            array(
                '%unknown_data_one% %name%\n'
                . '%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code% %unknown_data_two%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str. apartment 10\nNEW YORK NY UNITED STATES 12345"
            )
        );
    }
}
