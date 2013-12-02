<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Model;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfiguration;

class LocaleSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $calendarFactory;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->calendarFactory = $this->getMock('Oro\Bundle\LocaleBundle\Model\CalendarFactoryInterface');
        $this->localeSettings = new LocaleSettings($this->configManager, $this->calendarFactory);
    }

    public function testAddNameFormats()
    {
        $enFormat = '%first_name% %middle_name% %last_name%';
        $enFormatModified = '%prefix% %%first_name% %middle_name% %last_name% %suffix%';
        $ruFormat = '%last_name% %first_name% %middle_name%';

        $this->assertEmpty($this->localeSettings->getNameFormats());

        $this->localeSettings->addNameFormats(array('en' => $enFormat));
        $this->assertEquals(
            array('en' => $enFormat),
            $this->localeSettings->getNameFormats()
        );

        $this->localeSettings->addNameFormats(array('en' => $enFormatModified, 'ru' => $ruFormat));
        $this->assertEquals(
            array('en' => $enFormatModified, 'ru' => $ruFormat),
            $this->localeSettings->getNameFormats()
        );
    }

    public function testAddAddressFormats()
    {
        $usFormat = array(
            LocaleSettings::ADDRESS_FORMAT_KEY
                => '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%'
        );
        $usFormatModified = array(
            LocaleSettings::ADDRESS_FORMAT_KEY
                => '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%'
        );
        $ruFormat = array(
            LocaleSettings::ADDRESS_FORMAT_KEY
                => '%postal_code% %COUNTRY% %CITY%\n%STREET%\n%organization%\n%name%'
        );

        $this->assertEmpty($this->localeSettings->getAddressFormats());

        $this->localeSettings->addAddressFormats(array('US' => $usFormat));
        $this->assertEquals(
            array('US' => $usFormat),
            $this->localeSettings->getAddressFormats()
        );

        $this->localeSettings->addAddressFormats(array('US' => $usFormatModified, 'RU' => $ruFormat));
        $this->assertEquals(
            array('US' => $usFormatModified, 'RU' => $ruFormat),
            $this->localeSettings->getAddressFormats()
        );
    }

    public function testAddLocaleData()
    {
        $usData = array(LocaleSettings::DEFAULT_LOCALE_KEY => 'en_US');
        $usDataModified = array(LocaleSettings::DEFAULT_LOCALE_KEY => 'en');
        $ruData = array(LocaleSettings::DEFAULT_LOCALE_KEY => 'ru');

        $this->assertEmpty($this->localeSettings->getLocaleData());

        $this->localeSettings->addLocaleData(array('US' => $usData));
        $this->assertEquals(
            array('US' => $usData),
            $this->localeSettings->getLocaleData()
        );

        $this->localeSettings->addLocaleData(array('US' => $usDataModified, 'RU' => $ruData));
        $this->assertEquals(
            array('US' => $usDataModified, 'RU' => $ruData),
            $this->localeSettings->getLocaleData()
        );
    }

    public function testAddCurrencyData()
    {
        $usData = array(LocaleSettings::CURRENCY_SYMBOL_KEY => '$');
        $usDataModified = array(LocaleSettings::CURRENCY_SYMBOL_KEY => 'AU$');
        $ruData = array(LocaleSettings::CURRENCY_SYMBOL_KEY => 'руб.');

        $this->assertEmpty($this->localeSettings->getCurrencyData());

        $this->localeSettings->addCurrencyData(array('USD' => $usData));
        $this->assertEquals(
            array('USD' => $usData),
            $this->localeSettings->getCurrencyData()
        );

        $this->localeSettings->addCurrencyData(array('USD' => $usDataModified, 'RUR' => $ruData));
        $this->assertEquals(
            array('USD' => $usDataModified, 'RUR' => $ruData),
            $this->localeSettings->getCurrencyData()
        );
    }

    /**
     * @dataProvider getValidLocaleDataProvider
     */
    public function testGetValidLocale($locale, $expectedLocale)
    {
        $this->assertEquals($expectedLocale, LocaleSettings::getValidLocale($locale));
    }

    public function getValidLocaleDataProvider()
    {
        return array(
            array('ru_RU', 'ru_RU'),
            array('en', LocaleConfiguration::DEFAULT_LOCALE),
            array(null, LocaleConfiguration::DEFAULT_LOCALE),
            array('ru', 'ru'),
            array('en_Hans_CN_nedis_rozaj_x_prv1_prv2', 'en_US'),
            array('en_Hans_CA_nedis_rozaj_x_prv1_prv2', 'en_CA'),
            array('unknown', 'en_US'),
        );
    }

    /**
     * @dataProvider getCountryByLocaleDataProvider
     */
    public function testGetCountryByLocale($locale, $expectedCountry)
    {
        $this->assertEquals($expectedCountry, LocaleSettings::getCountryByLocale($locale));
    }

    public function getCountryByLocaleDataProvider()
    {
        return array(
            array('EN', LocaleConfiguration::DEFAULT_COUNTRY),
            array('RU', LocaleConfiguration::DEFAULT_COUNTRY),
            array('en_US', 'US'),
            array('en_XX', LocaleConfiguration::DEFAULT_COUNTRY),
        );
    }

    /**
     * @dataProvider getLocaleByCountryDataProvider
     */
    public function testGetLocaleByCountry(array $localeData, $countryCode, $expectedLocale, $defaultLocale = null)
    {
        $this->localeSettings->addLocaleData($localeData);

        if (null !== $defaultLocale) {
            $this->configManager->expects($this->once())
                ->method('get')
                ->with('oro_locale.locale')
                ->will($this->returnValue($defaultLocale));
        } else {
            $this->configManager->expects($this->never())->method($this->anything());
        }

        $this->assertEquals($expectedLocale, $this->localeSettings->getLocaleByCountry($countryCode));
    }

    public function getLocaleByCountryDataProvider()
    {
        return array(
            array(
                array('GB' => array(LocaleSettings::DEFAULT_LOCALE_KEY => 'en_GB')),
                'GB',
                'en_GB'
            ),
            array(
                array(),
                'GB',
                'en_US',
                'en_US'
            ),
        );
    }

    /**
     * @param string $expectedValue
     * @param string $configurationValue
     * @dataProvider getLocaleDataProvider
     */
    public function testGetLocale($expectedValue, $configurationValue)
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue($configurationValue));

        $this->assertEquals($expectedValue, $this->localeSettings->getLocale());
        $this->assertEquals($expectedValue, $this->localeSettings->getLocale());
    }

    /**
     * @return array
     */
    public function getLocaleDataProvider()
    {
        return array(
            'configuration value' => array(
                'expectedValue' => 'ru_RU',
                'configurationValue' => 'ru_RU',
            ),
            'default value' => array(
                'expectedValue' => LocaleConfiguration::DEFAULT_LOCALE,
                'configurationValue' => null,
            ),
        );
    }

    public function testGetCountry()
    {
        $expectedCountry = 'CA';

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.country')
            ->will($this->returnValue($expectedCountry));

        $this->assertEquals($expectedCountry, $this->localeSettings->getCountry());
        $this->assertEquals($expectedCountry, $this->localeSettings->getCountry());
    }

    public function testGetCountryDefault()
    {
        $expectedCountry = 'US';

        $this->configManager->expects($this->at(0))
            ->method('get')
            ->with('oro_locale.country')
            ->will($this->returnValue(null));

        $this->configManager->expects($this->at(1))
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue('en_US'));

        $this->assertEquals($expectedCountry, $this->localeSettings->getCountry());
        $this->assertEquals($expectedCountry, $this->localeSettings->getCountry());
    }

    /**
     * @param $expectedValue
     * @param $configurationValue
     * @dataProvider getTimeZoneDataProvider
     */
    public function testGetTimeZone($expectedValue, $configurationValue)
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.timezone', false)
            ->will($this->returnValue($configurationValue));

        $this->assertEquals($expectedValue, $this->localeSettings->getTimeZone());
        $this->assertEquals($expectedValue, $this->localeSettings->getTimeZone());
    }

    /**
     * @return array
     */
    public function getTimeZoneDataProvider()
    {
        return array(
            'configuration value' => array(
                'expectedValue' => 'America/Los_Angeles',
                'configurationValue' => 'America/Los_Angeles',
            ),
            'default value' => array(
                'expectedValue' => date_default_timezone_get(),
                'configurationValue' => null,
            ),
        );
    }

    public function testGetCurrency()
    {
        $expectedCurrency = 'GBP';

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.currency')
            ->will($this->returnValue($expectedCurrency));

        $this->assertEquals($expectedCurrency, $this->localeSettings->getCurrency());
        $this->assertEquals($expectedCurrency, $this->localeSettings->getCurrency());
    }

    public function testGetCurrencyDefault()
    {
        $expectedCurrency = LocaleConfiguration::DEFAULT_CURRENCY;

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.currency')
            ->will($this->returnValue(null));

        $this->assertEquals($expectedCurrency, $this->localeSettings->getCurrency());
        $this->assertEquals($expectedCurrency, $this->localeSettings->getCurrency());
    }

    public function testGetCalendarDefaultLocaleAndLanguage()
    {
        $expectedLocale = 'ru_RU';
        $expectedLanguage = 'fr_CA';

        $this->configManager->expects($this->at(0))
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue($expectedLocale));

        $this->configManager->expects($this->at(1))
            ->method('get')
            ->with('oro_locale.language')
            ->will($this->returnValue($expectedLanguage));

        $calendar = $this->getMock('Oro\Bundle\LocaleBundle\Model\Calendar');

        $this->calendarFactory->expects($this->once())->method('getCalendar')
            ->with($expectedLocale, $expectedLanguage)
            ->will($this->returnValue($calendar));

        $this->assertSame($calendar, $this->localeSettings->getCalendar());
    }

    public function testGetCalendarSpecificLocale()
    {
        $locale = 'ru_RU';
        $language = 'fr_CA';

        $this->configManager->expects($this->never())->method($this->anything());

        $calendar = $this->getMock('Oro\Bundle\LocaleBundle\Model\Calendar');

        $this->calendarFactory->expects($this->once())->method('getCalendar')
            ->with($locale, $language)
            ->will($this->returnValue($calendar));

        $this->assertSame($calendar, $this->localeSettings->getCalendar($locale, $language));
    }

    public function testIsFormatAddressByAddressCountry()
    {
        $this->configManager->expects($this->at(0))
            ->method('get')
            ->with('oro_locale.format_address_by_address_country')
            ->will($this->returnValue(''));
        $this->configManager->expects($this->at(1))
            ->method('get')
            ->with('oro_locale.format_address_by_address_country')
            ->will($this->returnValue('1'));

        $this->assertFalse($this->localeSettings->isFormatAddressByAddressCountry());
        $this->assertTrue($this->localeSettings->isFormatAddressByAddressCountry());
    }

    /**
     * @param string $expectedValue
     * @param string $configurationValue
     * @dataProvider getLanguageDataProvider
     */
    public function testGetLanguage($expectedValue, $configurationValue)
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.language')
            ->will($this->returnValue($configurationValue));

        $this->assertEquals($expectedValue, $this->localeSettings->getLanguage());
    }

    /**
     * @return array
     */
    public function getLanguageDataProvider()
    {
        return array(
            'configuration value' => array(
                'expectedValue' => 'ru',
                'configurationValue' => 'ru',
            ),
            'default value' => array(
                'expectedValue' => LocaleConfiguration::DEFAULT_LANGUAGE,
                'configurationValue' => null,
            ),
        );
    }

    public function testGetCurrencySymbolByCurrency()
    {
        $existingCurrencyCode = 'USD';
        $existingCurrencySymbol = '$';
        $notExistingCurrencyCode = 'UAK';

        $currencyData = array(
            $existingCurrencyCode => array('symbol' => $existingCurrencySymbol)
        );
        $this->localeSettings->addCurrencyData($currencyData);

        $this->assertEquals(
            $existingCurrencySymbol,
            $this->localeSettings->getCurrencySymbolByCurrency($existingCurrencyCode)
        );
        $this->assertEquals(
            $notExistingCurrencyCode,
            $this->localeSettings->getCurrencySymbolByCurrency($notExistingCurrencyCode)
        );
    }
}
