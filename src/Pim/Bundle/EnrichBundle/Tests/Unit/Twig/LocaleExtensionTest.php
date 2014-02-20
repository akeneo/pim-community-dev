<?php

namespace Pim\Bundle\EnrichBundle\Tests\Unit\Twig;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\EnrichBundle\Twig\LocaleExtension;

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
     * @var \Pim\Bundle\EnrichBundle\Twig\LocaleExtension
     */
    protected $localeExtension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->localeExtension = new LocaleExtension($this->getLocaleHelperMock());
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $this->assertEquals('pim_locale_extension', $this->localeExtension->getName());
    }

    /**
     * Test related method
     */
    public function testLocaleLabel()
    {
        $this->assertEquals('en_US', $this->localeExtension->localeLabel('en_US'));
    }

    /**
     * Test related method
     */
    public function testFlag()
    {
        $this->assertEquals('en_US', $this->localeExtension->flag('en_US'));
    }

    /**
     * Test related method
     */
    public function testCurrencySymbol()
    {
        $this->assertEquals('USD', $this->localeExtension->currencySymbol('USD'));
    }

    /**
     * Test related method
     */
    public function testGetFunctions()
    {
        $twigFunctions = $this->localeExtension->getFunctions();

        $this->assertArrayHasKey('locale_label', $twigFunctions);
        $this->assertTrue(method_exists($this->localeExtension, 'localeLabel'));
        $this->assertInstanceOf('\Twig_Function_Method', $twigFunctions['locale_label']);

        $this->assertArrayHasKey('currency_symbol', $twigFunctions);
        $this->assertTrue(method_exists($this->localeExtension, 'currencySymbol'));
        $this->assertInstanceOf('\Twig_Function_Method', $twigFunctions['currency_symbol']);
    }

    /**
     * Test related method
     */
    public function testGetFilters()
    {
        $twigFilters = $this->localeExtension->getFilters();

        $this->assertArrayHasKey('flag', $twigFilters);
        $this->assertTrue(method_exists($this->localeExtension, 'flag'));
        $this->assertInstanceOf('\Twig_Filter_Method', $twigFilters['flag']);
    }
    /**
     * Get LocaleHelperMock
     *
     * @return \Pim\Bundle\CatalogBundle\Helper\LocaleHelper
     */
    protected function getLocaleHelperMock()
    {
        $helper = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Helper\LocaleHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $helper->expects($this->any())
            ->method('getLocaleLabel')
            ->will($this->returnArgument(0));
        $helper->expects($this->any())
            ->method('getFlag')
            ->will($this->returnArgument(0));
        $helper->expects($this->any())
            ->method('getCurrencySymbol')
            ->will($this->returnArgument(0));

        return $helper;
    }
}
