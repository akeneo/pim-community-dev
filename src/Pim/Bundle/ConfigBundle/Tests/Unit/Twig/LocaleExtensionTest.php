<?php

namespace Pim\Bundle\ConfigBundle\Tests\Unit\Twig;

use Pim\Bundle\ConfigBundle\Helper\LocaleHelper;
use Pim\Bundle\ConfigBundle\Twig\LocaleExtension;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\ConfigBundle\Twig\LocaleExtension
     */
    protected $localeExtension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $localeHelper = $this->createLocaleHelper();
        $this->localeExtension = new LocaleExtension($localeHelper);
    }

    /**
     * Create locale helper
     *
     * @param string $userLocaleCode
     *
     * @return \Pim\Bundle\ConfigBundle\Helper\LocaleHelper
     */
    protected function createLocaleHelper()
    {
        $localeManager = $this->createLocaleManager();
        $localeHelper = new LocaleHelper($localeManager);

        return $localeHelper;
    }

    /**
     * Create locale manager
     *
     * @param string $userLocaleCode
     *
     * @return \Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected function createLocaleManager()
    {
        $localeManager = $this
            ->getMockBuilder('Pim\Bundle\ConfigBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock(array('getUserLocaleCode'));

        $localeManager
            ->expects($this->any())
            ->method('getUserLocaleCode')
            ->will($this->returnValue('en_US'));

        return $localeManager;
    }

    /**
     * Data provider for localized label
     *
     * @static
     *
     * @return array
     */
    public static function dataProviderForLocalizedLabel()
    {
        return array(
            'EN' => array(
                array(
                    'en_US' => 'English (United States)',
                    'en_EN' => 'English',
                    'fr_FR' => 'French'
                )
            )
        );
    }

    /**
     * Test related method
     *
     * @dataProvider dataProviderForLocalizedLabel
     *
     * @param array  $expectedResults
     */
    public function testLocalizedLabel($expectedResults)
    {
        foreach ($expectedResults as $code => $expectedResult) {
            $result = $this->localeExtension->localizedLabel($code);
            $this->assertEquals($expectedResult, $result);
        }
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $this->assertEquals('pim_locale_extension', $this->localeExtension->getName());
    }

    /**
     * Data provider with expected functions
     *
     * @static
     *
     * @return array
     */
    public static function dataProviderForExpectedFunctions()
    {
        return array(
            array('localizedLabel')
        );
    }

    /**
     * Test related method
     *
     * @dataProvider dataProviderForExpectedFunctions
     */
    public function testGetFunctions($expectedFunctions)
    {
        $twigFunctions = $this->localeExtension->getFunctions();

        foreach ($twigFunctions as $name => $twigFunction) {
            $this->assertEquals($expectedFunctions, $name);
            $this->assertTrue(method_exists($this->localeExtension, $name));
            $this->assertInstanceOf('\Twig_Function_Method', $twigFunction);
        }
    }
}
