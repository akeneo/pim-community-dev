<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\NotificationBundle\DependencyInjection\Compiler\EventsCompilerPass;

class EventsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testCompile()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $compiler = new EventsCompilerPass();
        $compiler->process($container);
    }
}
