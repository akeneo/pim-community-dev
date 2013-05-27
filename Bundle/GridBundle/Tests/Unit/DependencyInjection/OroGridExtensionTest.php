<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\GridBundle\DependencyInjection\OroGridExtension;

class OroGridExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->atLeastOnce())
            ->method('setDefinition');
        $container->expects($this->atLeastOnce())
            ->method('setParameter');

        $gridExtension = new OroGridExtension();
        $configs = array();
        $gridExtension->load($configs, $container);
    }
}
