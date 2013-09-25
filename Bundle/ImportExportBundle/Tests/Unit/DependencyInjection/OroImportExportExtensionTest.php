<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\ImportExportBundle\DependencyInjection\OroImportExportExtension;

class OroGridExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $gridExtension = new OroImportExportExtension();
        $configs = array();
        $gridExtension->load($configs, $container);
    }
}
