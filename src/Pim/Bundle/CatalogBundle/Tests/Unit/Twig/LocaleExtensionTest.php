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
        $this->localeExtension = new LocaleExtension($this->getContainerMock());
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
        $this->assertEquals('English (United States)', $this->localeExtension->localizedLabel('en_US'));
    }

    public function testGetFunctions()
    {
        $twigFunctions = $this->localeExtension->getFunctions();

        $this->assertArrayHasKey('localized_label', $twigFunctions);
        $this->assertTrue(method_exists($this->localeExtension, 'localizedLabel'));
        $this->assertInstanceOf('\Twig_Function_Method', $twigFunctions['localized_label']);
    }

    protected function getContainerMock()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->any())
            ->method('get')
            ->with($this->equalTo('request'))
            ->will($this->returnValue($request));

        $request->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue('en_US'));
        return $container;
    }

}
