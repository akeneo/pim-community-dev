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
     * @param bool $formatByCountry
     */
    public function testFormat($format, $expected, $formatByCountry = false)
    {
        $address = new AddressStub();
        $locale = 'en';
        $country = 'CA';

        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(
                array('getAddressFormat', 'getLocaleByCountry', 'getCountry', 'isFormatAddressByAddressCountry')
            )
            ->getMock();
        $localeSettings->expects($this->once())
            ->method('isFormatAddressByAddressCountry')
            ->will($this->returnValue($formatByCountry));
        if ($formatByCountry) {
            $localeSettings->expects($this->never())
                ->method('getCountry');
            $localeSettings->expects($this->once())
                ->method('getAddressFormat')
                ->with($address->getCountryIso2())
                ->will($this->returnValue($format));
            $localeSettings->expects($this->once())
                ->method('getLocaleByCountry')
                ->with($address->getCountryIso2())
                ->will($this->returnValue($locale));
        } else {
            $localeSettings->expects($this->once())
                ->method('getCountry')
                ->will($this->returnValue($country));
            $localeSettings->expects($this->once())
                ->method('getAddressFormat')
                ->with($country)
                ->will($this->returnValue($format));
            $localeSettings->expects($this->once())
                ->method('getLocaleByCountry')
                ->with($country)
                ->will($this->returnValue($locale));
        }

        $nameFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NameFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $nameFormatter->expects($this->once())
            ->method('format')
            ->with($address, $locale)
            ->will($this->returnValue('Formatted User NAME'));

        $formatter = new AddressFormatter($localeSettings, $nameFormatter);
        $this->assertEquals($expected, $formatter->format($address));
    }

    public function formatDataProvider()
    {
        return array(
            'simple street' => array(
                '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str. apartment 10\nNEW YORK NY UNITED STATES 12345"
            ),
            'complex street' => array(
                '%name%\n%organization%\n%street1%\n%street2%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str.\napartment 10\nNEW YORK NY UNITED STATES 12345"
            ),
            'unknown field' => array(
                '%unknown_data_one% %name%\n'
                . '%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code% %unknown_data_two%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str. apartment 10\nNEW YORK NY UNITED STATES 12345"
            ),
            'address country format' => array(
                '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str. apartment 10\nNEW YORK NY UNITED STATES 12345",
                true
            ),
        );
    }
}
