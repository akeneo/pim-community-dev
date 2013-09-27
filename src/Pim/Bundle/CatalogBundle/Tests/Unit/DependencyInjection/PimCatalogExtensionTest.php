<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogExtensionTest extends \PHPUnit_Framework_TestCase
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

        $this->extension = new PimCatalogExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('validator.mapping.loader.yaml_files_loader.mapping_files', array());
        $this->container->setParameter('fos_rest.exception.codes', array());
        $this->container->setParameter('fos_rest.exception.messages', array());
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
        $configCurrencies = $this->container->getParameter('pim_catalog.currencies');
        $this->assertCount(1, $configCurrencies);
        $this->assertArrayHasKey('currencies', $configCurrencies);
        $this->assertTrue(is_array($configCurrencies['currencies']));

        // assert validation configuration
        $yamlMappingFiles = $this->container->getParameter('validator.mapping.loader.yaml_files_loader.mapping_files');
        $this->assertGreaterThanOrEqual(5, count($yamlMappingFiles));

        // assert delete exception code and message
        $codes = $this->container->getParameter('fos_rest.exception.codes');
        $expectedCodes = array('Pim\Bundle\CatalogBundle\Exception\DeleteException' => 409);
        $this->assertEquals($expectedCodes, $codes);

        $messages = $this->container->getParameter('fos_rest.exception.messages');
        $expectedMessages = array('Pim\Bundle\CatalogBundle\Exception\DeleteException' => true);
        $this->assertEquals($expectedMessages, $messages);
    }
}
