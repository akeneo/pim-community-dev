<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;
use Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs\AddressStub;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfiguration;

class AddressFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $nameFormatter;

    /**
     * @var AddressFormatter
     */
    protected $addressFormatter;

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getLocale',
                    'getCountry',
                    'getAddressFormats',
                    'getLocaleByCountry',
                    'isFormatAddressByAddressCountry'
                )
            )
            ->getMock();

        $this->nameFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NameFormatter')
            ->disableOriginalConstructor()
            ->setMethods(array('format'))
            ->getMock();

        $this->addressFormatter = new AddressFormatter($this->localeSettings, $this->nameFormatter);
    }

    protected function tearDown()
    {
        unset($this->localeSettings);
        unset($this->nameFormatter);
        unset($this->addressFormatter);
    }

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
        $addressFormats = array(
            $country => array(
                LocaleSettings::ADDRESS_FORMAT_KEY => $format
            ),
        );

        $this->localeSettings->expects($this->once())
            ->method('getAddressFormats')
            ->will($this->returnValue($addressFormats));
        $this->localeSettings->expects($this->once())
            ->method('isFormatAddressByAddressCountry')
            ->will($this->returnValue($formatByCountry));
        $this->localeSettings->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue($country));
        if ($formatByCountry) {
            $this->localeSettings->expects($this->once())
                ->method('getLocaleByCountry')
                ->with($address->getCountryIso2())
                ->will($this->returnValue($locale));
        } else {
            $this->localeSettings->expects($this->once())
                ->method('getLocaleByCountry')
                ->with($country)
                ->will($this->returnValue($locale));
        }

        $this->nameFormatter->expects($this->once())
            ->method('format')
            ->with($address, $locale)
            ->will($this->returnValue('Formatted User NAME'));

        $this->assertEquals($expected, $this->addressFormatter->format($address));
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
            'multi spaces' => array(
                '%unknown_data_one% %name% %unknown_data_one%\n'
                . '%organization%\n%street%\n'
                . '%CITY% %unknown_data_one% %REGION_CODE% %COUNTRY% %postal_code% %unknown_data_two%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str. apartment 10\nNEW YORK NY UNITED STATES 12345"
            ),
            'address country format' => array(
                '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%',
                "Formatted User NAME\nCompany Ltd.\n1 Tests str. apartment 10\nNEW YORK NY UNITED STATES 12345",
                true
            ),
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot get address format for "CA"
     */
    public function testGetAddressFormatFails()
    {
        $this->localeSettings->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue(LocaleConfiguration::DEFAULT_COUNTRY));

        $this->addressFormatter->getAddressFormat('CA');
    }

    /**
     * @dataProvider getAddressFormatDataProvider
     *
     * @param array $addressFormats
     * @param string $localeOrRegion
     * @param string $expectedFormat
     * @param string $defaultCountry
     */
    public function testGetAddressFormat(
        array $addressFormats,
        $localeOrRegion,
        $expectedFormat,
        $defaultCountry = null
    ) {
        $this->localeSettings->expects($this->once())
            ->method('getAddressFormats')
            ->will($this->returnValue($addressFormats));

        if (!$localeOrRegion) {
            $this->localeSettings->expects($this->once())
                ->method('getLocale')
                ->will($this->returnValue('en_US'));
        }

        if ($defaultCountry) {
            $this->localeSettings->expects($this->once())
                ->method('getCountry')
                ->will($this->returnValue($defaultCountry));
        }

        $this->assertEquals($expectedFormat, $this->addressFormatter->getAddressFormat($localeOrRegion));
    }

    /**
     * @return array
     */
    public function getAddressFormatDataProvider()
    {
        return array(
            'direct' => array(
                'addressFormats' => array(
                    'US' => array(LocaleSettings::ADDRESS_FORMAT_KEY => '%address_format%')
                ),
                'localeOrRegion' => 'US',
                'expectedFormat' => '%address_format%'
            ),
            'parse_country' => array(
                'addressFormats' => array(
                    'CA' => array(LocaleSettings::ADDRESS_FORMAT_KEY => '%address_format%')
                ),
                'localeOrRegion' => 'fr_CA',
                'expectedFormat' => '%address_format%'
            ),
            'empty_locale_or_region' => array(
                'addressFormats' => array(
                    'RU' => array(LocaleSettings::ADDRESS_FORMAT_KEY => '%address_format%')
                ),
                'localeOrRegion' => false,
                'expectedFormat' => '%address_format%',
                'defaultCountry' => 'RU'
            ),
            'default_system_country' => array(
                'addressFormats' => array(
                    'RU' => array(LocaleSettings::ADDRESS_FORMAT_KEY => '%address_format%')
                ),
                'localeOrRegion' => 'fr_CA',
                'expectedFormat' => '%address_format%',
                'defaultCountry' => 'RU'
            ),
            'default_fallback' => array(
                'addressFormats' => array(
                    LocaleConfiguration::DEFAULT_COUNTRY => array(
                        LocaleSettings::ADDRESS_FORMAT_KEY => '%address_format%'
                    )
                ),
                'localeOrRegion' => 'fr_CA',
                'expectedFormat' => '%address_format%'
            ),
        );
    }
}
