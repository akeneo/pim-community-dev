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
            array('en_Hans_unknown', 'en'),
            array('en_Hans_CA_nedis_rozaj_x_prv1_prv2', 'en_CA'),
            array('bs_Latn_BA', 'bs_Latn_BA'),
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
            array('ru_RU', 'RU'),
            array('EN', LocaleConfiguration::DEFAULT_COUNTRY),
            array('RU', LocaleConfiguration::DEFAULT_COUNTRY),
            array('en_CA', 'CA'),
            array('en_CN', 'CN'),
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

    public function testGetLocale()
    {
        $expectedLocale = 'ru_RU';

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue($expectedLocale));

        $this->assertEquals($expectedLocale, $this->localeSettings->getLocale());
        $this->assertEquals($expectedLocale, $this->localeSettings->getLocale());
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

    public function testGetTimeZone()
    {
        $expectedTimeZone = 'America/Los_Angeles';

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.timezone', date_default_timezone_get())
            ->will($this->returnValue($expectedTimeZone));

        $this->assertEquals($expectedTimeZone, $this->localeSettings->getTimeZone());
        $this->assertEquals($expectedTimeZone, $this->localeSettings->getTimeZone());
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

    public function testGetCalendarDefaultLocale()
    {
        $expectedLocale = 'ru_RU';

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue($expectedLocale));

        $calendar = $this->getMock('Oro\Bundle\LocaleBundle\Model\Calendar');

        $this->calendarFactory->expects($this->once())->method('getCalendar')
            ->with($expectedLocale)
            ->will($this->returnValue($calendar));

        $this->assertSame($calendar, $this->localeSettings->getCalendar());
    }

    public function testGetCalendarSpecificLocale()
    {
        $locale = 'ru_RU';

        $this->configManager->expects($this->never())->method($this->anything());

        $calendar = $this->getMock('Oro\Bundle\LocaleBundle\Model\Calendar');

        $this->calendarFactory->expects($this->once())->method('getCalendar')
            ->with($locale)
            ->will($this->returnValue($calendar));

        $this->assertSame($calendar, $this->localeSettings->getCalendar($locale));
    }
}
