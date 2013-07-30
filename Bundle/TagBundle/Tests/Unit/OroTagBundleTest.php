<?php
namespace Oro\Bundle\TagBundle\Tests\Unit;

use Oro\Bundle\TagBundle\OroTagBundle;

class OroTagBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $bundle = new OroTagBundle();
        $bundle->build($container);
    }
}
