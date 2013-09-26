<?php

namespace Oro\Bundle\NotificationBundle\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

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

        if (!$container->hasParameter('installed') || !$container->getParameter('installed')) {
            return;
        }

        $eventClassName = $container->getParameter('oro_notification.event_entity.class');

        $dispatcher = $container->getDefinition(self::DISPATCHER_KEY);
        $em         = $container->get('doctrine.orm.entity_manager');

        $eventNames = array();
        if ($this->checkDatabase($em, $eventClassName) !== false) {
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
     * @param EntityManager $em
     * @param  string       $className
     *
     * @return bool
     */
    public function checkDatabase(EntityManager $em, $className)
    {
        $table  = $em->getClassMetadata($className)->getTableName();
        $result = false;
        try {
            $conn = $em->getConnection();

            if (!$conn->isConnected()) {
                $em->getConnection()->connect();
            }

            $result = $conn->isConnected() && (bool)array_intersect(
                array($table),
                $em->getConnection()->getSchemaManager()->listTableNames()
            );
        } catch (\PDOException $e) {
        }

        return $result;
    }
}
