<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Model;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class LocaleSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeSettings = new LocaleSettings($this->configManager);
    }

    public function testAddNameFormats()
    {
        $enFormat = '%first_name% %middle_name% %last_name%';
        $enFormatModified = '%prefix% %%first_name% %middle_name% %last_name% %suffix%';
        $ruFormat = '%last_name% %first_name% %middle_name%';

        $this->assertAttributeEmpty('nameFormats', $this->localeSettings);

        $this->localeSettings->addNameFormats(array('en' => $enFormat));
        $this->assertAttributeEquals(
            array('en' => $enFormat),
            'nameFormats',
            $this->localeSettings
        );

        $this->localeSettings->addNameFormats(array('en' => $enFormatModified, 'ru' => $ruFormat));
        $this->assertAttributeEquals(
            array('en' => $enFormatModified, 'ru' => $ruFormat),
            'nameFormats',
            $this->localeSettings
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

        $this->assertAttributeEmpty('addressFormats', $this->localeSettings);

        $this->localeSettings->addAddressFormats(array('US' => $usFormat));
        $this->assertAttributeEquals(
            array('US' => $usFormat),
            'addressFormats',
            $this->localeSettings
        );

        $this->localeSettings->addAddressFormats(array('US' => $usFormatModified, 'RU' => $ruFormat));
        $this->assertAttributeEquals(
            array('US' => $usFormatModified, 'RU' => $ruFormat),
            'addressFormats',
            $this->localeSettings
        );
    }

    public function testAddLocaleData()
    {
        $usData = array(LocaleSettings::DEFAULT_LOCALE => 'en_US');
        $usDataModified = array(LocaleSettings::DEFAULT_LOCALE => 'en');
        $ruFormat = array(LocaleSettings::DEFAULT_LOCALE => 'ru');

        $this->assertAttributeEmpty('localeData', $this->localeSettings);

        $this->localeSettings->addLocaleData(array('US' => $usData));
        $this->assertAttributeEquals(
            array('US' => $usData),
            'localeData',
            $this->localeSettings
        );

        $this->localeSettings->addLocaleData(array('US' => $usDataModified, 'RU' => $ruFormat));
        $this->assertAttributeEquals(
            array('US' => $usDataModified, 'RU' => $ruFormat),
            'localeData',
            $this->localeSettings
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot get name format for "fr_CA"
     */
    public function testGetNameFormatFails()
    {
        $this->localeSettings->getNameFormat('fr_CA');
    }

    /**
     * @dataProvider getNameFormatDataProvider
     *
     * @param array $nameFormats
     * @param string $locale
     * @param string $expectedFormat
     * @param string $defaultLocale
     */
    public function testGetNameFormat(array $nameFormats, $locale, $expectedFormat, $defaultLocale = null)
    {
        $this->localeSettings->addNameFormats($nameFormats);

        if (null !== $defaultLocale) {
            $this->configManager->expects($this->once())
                ->method('get')
                ->with('oro_locale.locale')
                ->will($this->returnValue($defaultLocale));
        } else {
            $this->configManager->expects($this->never())->method($this->anything());
        }

        $this->assertEquals($expectedFormat, $this->localeSettings->getNameFormat($locale));
    }

    /**
     * @return array
     */
    public function getNameFormatDataProvider()
    {
        return array(
            'direct' => array(
                'nameFormats' => array(
                    'en_US' => '%name_format%'
                ),
                'locale' => 'en_US',
                'expectedFormat' => '%name_format%'
            ),
            'parse_language' => array(
                'nameFormats' => array(
                    'fr' => '%name_format%'
                ),
                'locale' => 'fr_CA',
                'expectedFormat' => '%name_format%'
            ),
            'empty_locale' => array(
                'nameFormats' => array(
                    'en_US' => '%name_format%'
                ),
                'locale' => false,
                'expectedFormat' => '%name_format%',
                'defaultLocale' => 'en_US'
            ),
            'default_system_locale' => array(
                'nameFormats' => array(
                    'en_US' => '%name_format%'
                ),
                'locale' => 'fr_CA',
                'expectedFormat' => '%name_format%',
                'defaultLocale' => 'en_US'
            ),
            'default_fallback' => array(
                'nameFormats' => array(
                    LocaleSettings::DEFAULT_LOCALE => '%name_format%'
                ),
                'locale' => 'fr_CA',
                'expectedFormat' => '%name_format%',
                'defaultLocale' => ''
            ),
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot get address format for "CA"
     */
    public function testGetAddressFormatFails()
    {
        $this->localeSettings->getAddressFormat('CA');
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
        $this->localeSettings->addAddressFormats($addressFormats);

        $callIndex = 0;

        if (!$localeOrRegion) {
            $this->configManager->expects($this->at($callIndex++))
                ->method('get')
                ->with('oro_locale.locale')
                ->will($this->returnValue('en_US'));
        }

        if (null !== $defaultCountry) {
            if (!$callIndex) {
                $this->configManager->expects($this->at($callIndex++))
                    ->method('get')
                    ->with('oro_locale.locale')
                    ->will($this->returnValue('en_US'));
            }
            $this->configManager->expects($this->at($callIndex++))
                ->method('get')
                ->with('oro_locale.country')
                ->will($this->returnValue($defaultCountry));
        }

        $this->assertEquals($expectedFormat, $this->localeSettings->getAddressFormat($localeOrRegion));
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
                    LocaleSettings::DEFAULT_COUNTRY => array(
                        LocaleSettings::ADDRESS_FORMAT_KEY => '%address_format%'
                    )
                ),
                'localeOrRegion' => 'fr_CA',
                'expectedFormat' => '%address_format%'
            ),
        );
    }

    /**
     * @dataProvider getNumberFormatterAttributeDataProvider
     */
    public function testGetNumberFormatterAttribute($attribute, $locale, $style, $expected)
    {
        $this->assertSame(
            $expected,
            LocaleSettings::getNumberFormatterAttribute(
                $attribute,
                $locale,
                $style
            )
        );
    }

    public function getNumberFormatterAttributeDataProvider()
    {
        return array(
            array(\NumberFormatter::PARSE_INT_ONLY, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::GROUPING_USED, 'en_US', \NumberFormatter::DECIMAL, 1),
            array(\NumberFormatter::DECIMAL_ALWAYS_SHOWN, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::MAX_INTEGER_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 309),
            array(\NumberFormatter::MIN_INTEGER_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 1),
            array(\NumberFormatter::INTEGER_DIGITS,'en_US', \NumberFormatter::DECIMAL, 1),
            array(\NumberFormatter::MAX_FRACTION_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 3),
            array(\NumberFormatter::MIN_FRACTION_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::FRACTION_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::MULTIPLIER, 'en_US', \NumberFormatter::DECIMAL, 1),
            array(\NumberFormatter::GROUPING_SIZE, 'en_US', \NumberFormatter::DECIMAL, 3),
            array(\NumberFormatter::ROUNDING_MODE, 'en_US', \NumberFormatter::DECIMAL, 4),
            array(\NumberFormatter::ROUNDING_INCREMENT, 'en_US', \NumberFormatter::DECIMAL, 0.0),
            array(\NumberFormatter::FORMAT_WIDTH, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::PADDING_POSITION, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::SECONDARY_GROUPING_SIZE, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::SIGNIFICANT_DIGITS_USED, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::MIN_SIGNIFICANT_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 1),
            array(\NumberFormatter::MAX_SIGNIFICANT_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 6),
        );
    }

    /**
     * @dataProvider getNumberFormatterTextAttributeDataProvider
     */
    public function testGetNumberFormatterTestAttribute($attribute, $locale, $style, $expected)
    {
        $this->assertSame(
            $expected,
            LocaleSettings::getNumberFormatterTextAttribute(
                $attribute,
                $locale,
                $style
            )
        );
    }

    public function getNumberFormatterTextAttributeDataProvider()
    {
        return array(
            array(\NumberFormatter::POSITIVE_PREFIX, 'en_US', \NumberFormatter::DECIMAL, ''),
            array(\NumberFormatter::POSITIVE_SUFFIX, 'en_US', \NumberFormatter::DECIMAL, ''),
            array(\NumberFormatter::NEGATIVE_PREFIX, 'en_US', \NumberFormatter::DECIMAL, '-'),
            array(\NumberFormatter::NEGATIVE_SUFFIX, 'en_US', \NumberFormatter::DECIMAL, ''),
            array(\NumberFormatter::PADDING_CHARACTER, 'en_US', \NumberFormatter::DECIMAL, '*'),
            array(\NumberFormatter::CURRENCY_CODE, 'en_US', \NumberFormatter::CURRENCY, 'USD'),
            //array(\NumberFormatter::DEFAULT_RULESET, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::PUBLIC_RULESETS, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            array(\NumberFormatter::PATTERN_SEPARATOR_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, '-'),
            array(\NumberFormatter::PERCENT_SYMBOL, 'en_US', \NumberFormatter::PERCENT, '%'),
            array(\NumberFormatter::ZERO_DIGIT_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, '*'),
            array(\NumberFormatter::DIGIT_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, ''),
            //array(\NumberFormatter::MINUS_SIGN_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::PLUS_SIGN_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::CURRENCY_SYMBOL, 'en_US', \NumberFormatter::CURRENCY, false),
            //array(\NumberFormatter::INTL_CURRENCY_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::EXPONENTIAL_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::PERMILL_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::PAD_ESCAPE_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::INFINITY_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::NAN_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::SIGNIFICANT_DIGIT_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
            //array(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, 'en_US', \NumberFormatter::DECIMAL, false),
        );
    }

    /**
     * @dataProvider getDatePatternDataProvider
     */
    public function testGetDatePattern($locale, $dateType, $timeType, $expected)
    {
        $this->assertEquals($expected, $this->localeSettings->getDatePattern($locale, $dateType, $timeType));
    }

    public function getDatePatternDataProvider()
    {
        return array(
            array('en_US', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'EEEE, MMMM d, y h:mm:ss a zzzz'),
            array('ru_RU', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'EEEE, d MMMM y \'г\'. H:mm:ss zzzz'),
            array('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'EEEE d MMMM y HH:mm:ss zzzz'),
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
            array('en', LocaleSettings::DEFAULT_LOCALE),
            array(null, LocaleSettings::DEFAULT_LOCALE),
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
            array('EN', LocaleSettings::DEFAULT_COUNTRY),
            array('RU', LocaleSettings::DEFAULT_COUNTRY),
            array('en_CA', 'CA'),
            array('en_CN', 'CN'),
            array('en_XX', LocaleSettings::DEFAULT_COUNTRY),
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

        $this->configManager->expects($this->at(0))
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue('en_US'));

        $this->configManager->expects($this->at(1))
            ->method('get')
            ->with('oro_locale.country', 'US')
            ->will($this->returnValue($expectedCountry));

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
        $expectedCurrency = 'USD';

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_locale.currency', LocaleSettings::DEFAULT_CURRENCY)
            ->will($this->returnValue($expectedCurrency));

        $this->assertEquals($expectedCurrency, $this->localeSettings->getCurrency());
        $this->assertEquals($expectedCurrency, $this->localeSettings->getCurrency());
    }
}
