<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\EntityConfigBundle\OroEntityConfigBundle;

class OroEntityConfigBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var Bundle
     */
    private $bundle;

    protected function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->bundle           = new OroEntityConfigBundle();
    }

    public function testBuild()
    {
        $this->bundle->build($this->containerBuilder);

        $pass = $this->containerBuilder->getCompilerPassConfig()->getBeforeOptimizationPasses();

        $this->assertInstanceOf('Oro\Bundle\EntityConfigBundle\DependencyInjection\Compiler\EntityConfigPass', $pass[0]);
        $this->assertInstanceOf('Oro\Bundle\EntityConfigBundle\DependencyInjection\Compiler\ServiceProxyPass', $pass[1]);
    }
}
