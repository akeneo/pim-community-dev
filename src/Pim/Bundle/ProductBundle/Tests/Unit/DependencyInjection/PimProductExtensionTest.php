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
    protected $containerBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->extension = new PimProductExtension();
        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * Test related method
     */
    public function testLoad()
    {
        $this->extension->load($this->configs, $this->containerBuilder);

        $this->assertGreaterThanOrEqual(1, $this->containerBuilder->getServiceIds());
    }
}
