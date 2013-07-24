<?php

namespace Oro\Bundle\NotificationBundle\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class EventsCompilerPass implements CompilerPassInterface
{
    const SERVICE_KEY        = 'oro_notification.manager';
    const DISPATCHER_KEY     = 'event_dispatcher';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $eventClassName = $container->getParameter('oro_notification.event_entity.class');

        $dispatcher = $container->getDefinition(self::DISPATCHER_KEY);
        $em = $container->get('doctrine.orm.entity_manager');

        $eventNames = array();
        if ($this->isSchemaSynced($em, $eventClassName) !== false) {
            $eventNames = $em->getRepository($eventClassName)
                ->getEventNames();
        }
        foreach ($eventNames as $eventName) {
            $dispatcher->addMethodCall(
                'addListenerService',
                array($eventName['name'], array(self::SERVICE_KEY, 'process'))
            );
        }
    }

    /**
     * Returns false if database schema is not created correctly
     *
     * @param EntityManager $em
     * @param string $className
     * @return int|bool
     */
    protected function isSchemaSynced(EntityManager $em, $className)
    {
        $tables = $em->getConnection()->getSchemaManager()->listTableNames();
        $table = $em->getClassMetadata($className)->getTableName();

        return array_search($table, $tables);
    }
}
