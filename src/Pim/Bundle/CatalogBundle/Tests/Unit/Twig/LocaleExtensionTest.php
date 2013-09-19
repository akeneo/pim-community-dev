<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Twig;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Twig\LocaleExtension;

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
     * @var \Pim\Bundle\CatalogBundle\Twig\LocaleExtension
     */
    protected $localeExtension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $localeHelper          = $this->getLocaleHelperMock();
        $localeManager         = $this->getLocaleManagerMock('fr_FR');
        $this->localeExtension = new LocaleExtension($localeManager, $localeHelper);
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $this->assertEquals('pim_locale_extension', $this->localeExtension->getName());
    }

    public function testLocalizedLabel()
    {
        $this->assertEquals('fr_FR', $this->localeExtension->localizedLabel('fr_FR'));
    }

    public function testGetFunctions()
    {
        $twigFunctions = $this->localeExtension->getFunctions();

        $this->assertArrayHasKey('localizedLabel', $twigFunctions);
        $this->assertTrue(method_exists($this->localeExtension, 'localizedLabel'));
        $this->assertInstanceOf('\Twig_Function_Method', $twigFunctions['localizedLabel']);
    }

    protected function getLocaleHelperMock()
    {
        $helper = $this->getMock('Pim\Bundle\CatalogBundle\Helper\LocaleHelper');

        $helper->expects($this->any())
            ->method('getLocalizedLabel')
            ->will($this->returnArgument(0));

        return $helper;
    }

    /**
     * Create locale manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManagerMock($code)
    {
        $localeManager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $localeManager
            ->expects($this->any())
            ->method('getUserLocaleCode')
            ->will($this->returnValue($code));

        return $localeManager;
    }
}
