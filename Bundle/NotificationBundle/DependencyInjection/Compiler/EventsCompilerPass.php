<?php

namespace Oro\Bundle\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventsCompilerPass implements CompilerPassInterface
{
    const SERVICE_KEY    = 'oro_notification.manager';
    const DISPATCHER_KEY = 'event_dispatcher';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $dispatcher = $container->getDefinition(self::DISPATCHER_KEY);

        $eventNames = $container->get('doctrine.orm.entity_manager')
            ->getRepository('Oro\Bundle\NotificationBundle\Entity\Event')
            ->getEventNames();

        foreach ($eventNames as $eventName) {
            $dispatcher->addMethodCall('addListenerService', array($eventName['name'], array(self::SERVICE_KEY, 'process')));
        }
    }
}
