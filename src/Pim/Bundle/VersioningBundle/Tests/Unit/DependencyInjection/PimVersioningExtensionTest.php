<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\VersioningBundle\DependencyInjection\PimVersioningExtension;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimVersioningExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\DependencyInjection\Extension $extension
     */
    protected $extension;

    /**
     * @var array $configs
     */
    protected $configs = array();

    /**
     * @var ContainerBuilder $containerBuilder
     */
    protected $containerBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->extension = new PimVersioningExtension();
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
