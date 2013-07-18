<?php

namespace Oro\Bundle\NotificationBundle\DependencyInjection\Compiler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class EventsCompilerPass implements CompilerPassInterface
{
    const SERVICE_KEY    = 'oro_notification.manager';
    const DISPATCHER_KEY = 'event_dispatcher';
    const EVENT_ENTITY_NAME = 'Oro\Bundle\NotificationBundle\Entity\Event';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $dispatcher = $container->getDefinition(self::DISPATCHER_KEY);
        $em = $container->get('doctrine.orm.entity_manager');

        $eventNames = array();
        if ($this->isSchemaSynced($em)) {
            $eventNames = $em->getRepository(self::EVENT_ENTITY_NAME)
                ->getEventNames();
        }

        foreach ($eventNames as $eventName) {
            $dispatcher->addMethodCall(
                'addListenerService',
                array($eventName['name'], array(self::SERVICE_KEY, 'process'))
            );
        }
    }

    protected function isSchemaSynced(ObjectManager $em)
    {
        $tables = $em->getConnection()->getSchemaManager()->listTableNames();
        $table = $em->getClassMetadata(self::EVENT_ENTITY_NAME)->getTableName();

        return array_search($table, $tables);
    }
}
