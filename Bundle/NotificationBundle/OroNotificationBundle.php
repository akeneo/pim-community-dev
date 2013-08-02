<?php

namespace Oro\Bundle\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

use Oro\Bundle\NotificationBundle\DependencyInjection\Compiler\EventsCompilerPass;
use Oro\Bundle\NotificationBundle\DependencyInjection\Compiler\NotificationHandlerPass;

class OroNotificationBundle extends Bundle
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new NotificationHandlerPass())
            ->addCompilerPass(new EventsCompilerPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
