<?php

namespace Oro\Bundle\HelpBundle\Tests\Unit;

use Oro\Bundle\HelpBundle\OroHelpBundle;

class OroHelpBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $bundle = new OroHelpBundle();
        $bundle->build($containerBuilder);
    }
}
