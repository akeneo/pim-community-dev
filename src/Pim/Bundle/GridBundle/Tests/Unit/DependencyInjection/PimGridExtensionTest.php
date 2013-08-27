<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\GridBundle\DependencyInjection\PimGridExtension;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimGridExtensionTest extends \PHPUnit_Framework_TestCase
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

        $this->extension = new PimGridExtension();
        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * Test related method
     */
    public function testLoad()
    {
        $expectedFilters = array(
            'pim_grid.orm.filter.type.currency',
            'pim_grid.orm.filter.type.entity',
            'pim_grid.orm.filter.type.scope'
        );

        $this->extension->load($this->configs, $this->containerBuilder);

        $this->assertCount(4, $this->containerBuilder->getServiceIds());
        foreach ($expectedFilters as $expectedFilter) {
            $this->assertContains($expectedFilter, $this->containerBuilder->getServiceIds());
        }
    }
}
