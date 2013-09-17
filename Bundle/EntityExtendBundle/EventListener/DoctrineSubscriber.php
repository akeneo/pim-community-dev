<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\Generator;

class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata'
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /** @var OroEntityManager $em */
        $em = $event->getEntityManager();

        $configProvider = $em->getExtendManager()->getConfigProvider();
        $className      = $event->getClassMetadata()->getName();

        if ($configProvider->hasConfig($className)) {
            $config = $configProvider->getConfig($className);
            if ($config->is('is_extend') && $config->is('index')) {
                $index = isset($event->getClassMetadata()->table['indexes'])
                    ? $event->getClassMetadata()->table['indexes']
                    : array();

                foreach ($config->get('index') as $columnName => $enabled) {
                    if ($enabled) {
                        $tableName = strtolower(str_replace('\\', '_', $event->getClassMetadata()->getName()));
                        $indexName = 'oro_' . $tableName . '_' . $columnName;

                        $index[$indexName] = array('columns' => array(Generator::PREFIX . $columnName));
                    }
                }

                $event->getClassMetadata()->table['indexes'] = $index;
            }
        }
    }
}
