<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\ProductBundle\DependencyInjection\PimProductExtension;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class PimProductExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\DependencyInjection\Extension
     */
    protected $extension;

    /**
     * @var multitype:mixed
     */
    protected $configs = array();

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->extension = new PimProductExtension();
        $this->container = new ContainerBuilder();
    }

    /**
     * Test related method
     */
    public function testLoad()
    {
        $this->assertCount(1, $this->container->getServiceIds());
        $this->extension->load($this->configs, $this->container);
        $this->assertGreaterThanOrEqual(1, $this->container->getServiceIds());

        // assert currency configuration
        $configCurrencies = $this->container->getParameter('pim_product.currencies');
        $this->assertCount(1, $configCurrencies);
        $this->assertArrayHasKey('currencies', $configCurrencies);
        $this->assertTrue(is_array($configCurrencies['currencies']));

        // assert locale configuration
        $configLocales = $this->container->getParameter('pim_product.locales');
        $this->assertCount(1, $configLocales);
        $this->assertArrayHasKey('locales', $configLocales);
        $this->assertTrue(is_array($configLocales['locales']));
    }
}
