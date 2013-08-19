<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Twig;

use Pim\Bundle\ProductBundle\Helper\LocaleHelper;
use Pim\Bundle\ProductBundle\Twig\LocaleExtension;

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
     * @var \Pim\Bundle\ProductBundle\Twig\LocaleExtension
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
     * @return \Pim\Bundle\ProductBundle\Helper\LocaleHelper
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
     * @return \Pim\Bundle\ProductBundle\Manager\LocaleManager
     */
    protected function createLocaleManager()
    {
        $localeManager = $this
            ->getMockBuilder('Pim\Bundle\ProductBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock(array('getUserLocaleCode'));

        $localeManager
            ->expects($this->any())
            ->method('getUserLocaleCode')
            ->will($this->returnValue('fr_FR'));

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
            'FR' => array(
                array(
                    'en_US' => 'anglais (États-Unis)',
                    'en_EN' => 'anglais',
                    'fr_FR' => 'français'
                )
            )
        );
    }

    /**
     * Test related method
     *
     * @param array $expectedResults
     *
     * @dataProvider dataProviderForLocalizedLabel
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
     * @param string $expectedFunctions
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
