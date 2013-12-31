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
    }

    /**
     * Test related method
     */
    public function testLoad()
    {
        $this->assertCount(1, $this->container->getServiceIds());
        $this->extension->load($this->configs, $this->container);
        $this->assertGreaterThanOrEqual(1, $this->container->getServiceIds());
    }
}
