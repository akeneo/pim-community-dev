<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\NotificationBundle\DependencyInjection\Compiler\TemplatesCompilerPass;

class TemplatesCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testCompile()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');

        $bundle->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/../../Fixtures'));

        $kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array($bundle)));

        $container->expects($this->once())
            ->method('setParameter')
            ->with(
                'oro_notification.emailnotification.templates_list',
                $this->arrayHasKey('@'. $bundle->getName() . ':test.template.html.twig')
            );

        $compiler = new TemplatesCompilerPass($kernel);
        $compiler->process($container);
    }
}
