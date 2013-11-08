<?php

namespace Oro\Bundle\LocaleBundle\Test\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfiguration;
use Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs\PersonAllNamePartsStub;
use Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs\PersonFullNameStub;

class NameFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var NameFormatter
     */
    protected $formatter;

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocale', 'getNameFormats'))
            ->getMock();

        $this->formatter = new NameFormatter($this->localeSettings);
    }

    protected function tearDown()
    {
        unset($this->localeSettings);
        unset($this->formatter);
    }

    /**
     * @dataProvider formatDataProvider
     * @param string $format
     * @param string $expected
     * @param object $person
     */
    public function testFormat($format, $expected, $person)
    {
        $this->localeSettings->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue(LocaleConfiguration::DEFAULT_LOCALE));
        $this->localeSettings->expects($this->once())
            ->method('getNameFormats')
            ->will($this->returnValue(array(LocaleConfiguration::DEFAULT_LOCALE => $format)));

        $this->assertEquals($expected, $this->formatter->format($person));
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
            ),
            array(
                '%last_name% %unknown_data_one% %FIRST_NAME% %middle_name% %PREFIX% %suffix%',
                'ln FN mn NP ns',
                new PersonAllNamePartsStub()
            ),
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot get name format for "fr_CA"
     */
    public function testGetNameFormatFails()
    {
        $this->localeSettings->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue(LocaleConfiguration::DEFAULT_LOCALE));

        $this->formatter->getNameFormat('fr_CA');
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
        $this->localeSettings->expects($this->once())
            ->method('getNameFormats')
            ->will($this->returnValue($nameFormats));

        if (null !== $defaultLocale) {
            $this->localeSettings->expects($this->once())
                ->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        } else {
            $this->localeSettings->expects($this->never())
                ->method('getLocale');
        }

        $this->assertEquals($expectedFormat, $this->formatter->getNameFormat($locale));
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
                    LocaleConfiguration::DEFAULT_LOCALE => '%name_format%'
                ),
                'locale' => 'fr_CA',
                'expectedFormat' => '%name_format%',
                'defaultLocale' => ''
            ),
        );
    }
}
