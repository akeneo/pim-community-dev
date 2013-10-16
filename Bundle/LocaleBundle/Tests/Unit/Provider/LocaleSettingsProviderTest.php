<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Provider;

use Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider;

class LocaleSettingsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var LocaleSettingsProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = new LocaleSettingsProvider($this->configManager);
    }

    public function testAddNameFormats()
    {
        $enFormat = '%first_name% %middle_name% %last_name%';
        $enFormatModified = '%prefix% %%first_name% %middle_name% %last_name% %suffix%';
        $ruFormat = '%last_name% %first_name% %middle_name%';

        $this->assertAttributeEmpty('nameFormats', $this->provider);

        $this->provider->addNameFormats(array('en' => $enFormat));
        $this->assertAttributeEquals(
            array('en' => $enFormat),
            'nameFormats',
            $this->provider
        );

        $this->provider->addNameFormats(array('en' => $enFormatModified, 'ru' => $ruFormat));
        $this->assertAttributeEquals(
            array('en' => $enFormatModified, 'ru' => $ruFormat),
            'nameFormats',
            $this->provider
        );
    }

    public function testAddAddressFormats()
    {
        $usFormat = '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%';
        $usFormatModified = '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%';
        $ruFormat = '%postal_code% %COUNTRY% %CITY%\n%STREET%\n%organization%\n%name%';

        $this->assertAttributeEmpty('addressFormats', $this->provider);

        $this->provider->addAddressFormats(array('US' => $usFormat));
        $this->assertAttributeEquals(
            array('US' => $usFormat),
            'addressFormats',
            $this->provider
        );

        $this->provider->addAddressFormats(array('US' => $usFormatModified, 'RU' => $ruFormat));
        $this->assertAttributeEquals(
            array('US' => $usFormatModified, 'RU' => $ruFormat),
            'addressFormats',
            $this->provider
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot get name format for "fr_CA"
     */
    public function testGetNameFormatFails()
    {
        $this->provider->getNameFormat('fr_CA');
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
        $this->provider->addNameFormats($nameFormats);
        $this->provider->setDefaultLocale($defaultLocale);
        $this->assertEquals($expectedFormat, $this->provider->getNameFormat($locale));
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
                    LocaleSettingsProvider::DEFAULT_LOCALE => '%name_format%'
                ),
                'locale' => 'fr_CA',
                'expectedFormat' => '%name_format%'
            ),
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot get address format for "CA"
     */
    public function testGetAddressFormatFails()
    {
        $this->provider->getAddressFormat('CA');
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
        $this->provider->addAddressFormats($addressFormats);
        $this->provider->setDefaultCountry($defaultCountry);
        $this->assertEquals($expectedFormat, $this->provider->getAddressFormat($localeOrRegion));
    }

    /**
     * @return array
     */
    public function getAddressFormatDataProvider()
    {
        return array(
            'direct' => array(
                'addressFormats' => array(
                    'US' => '%address_format%'
                ),
                'localeOrRegion' => 'US',
                'expectedFormat' => '%address_format%'
            ),
            'parse_country' => array(
                'addressFormats' => array(
                    'CA' => '%address_format%'
                ),
                'localeOrRegion' => 'fr_CA',
                'expectedFormat' => '%address_format%'
            ),
            'empty_locale_or_region' => array(
                'addressFormats' => array(
                    'RU' => '%address_format%'
                ),
                'localeOrRegion' => false,
                'expectedFormat' => '%address_format%',
                'defaultCountry' => 'RU'
            ),
            'default_system_country' => array(
                'addressFormats' => array(
                    'RU' => '%address_format%'
                ),
                'localeOrRegion' => 'fr_CA',
                'expectedFormat' => '%address_format%',
                'defaultCountry' => 'RU'
            ),
            'default_fallback' => array(
                'addressFormats' => array(
                    LocaleSettingsProvider::DEFAULT_COUNTRY => '%address_format%'
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
        $this->assertEquals(
            $expected,
            LocaleSettingsProvider::getNumberFormatterAttribute(
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
            array(\NumberFormatter::ROUNDING_INCREMENT, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::FORMAT_WIDTH, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::PADDING_POSITION, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::SECONDARY_GROUPING_SIZE, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::SIGNIFICANT_DIGITS_USED, 'en_US', \NumberFormatter::DECIMAL, 0),
            array(\NumberFormatter::MIN_SIGNIFICANT_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 1),
            array(\NumberFormatter::MAX_SIGNIFICANT_DIGITS, 'en_US', \NumberFormatter::DECIMAL, 6),
        );
    }
}
