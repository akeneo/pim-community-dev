<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TagBundle\DependencyInjection\OroTagExtension;

class OroTagExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $extension = new OroTagExtension();
        $configs = array();
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $extension->load($configs, $container);
    }
}
